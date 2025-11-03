<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Order;

use App\Application\Service\AliExpress\AeUtil;
use App\Application\Service\AliExpress\DropshipperServiceInterface;
use App\Application\Service\Country\CountryServiceInterface;
use App\Application\Service\TranslatorInterface;
use App\Application\Shared\NumberHelper;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Language\LanguageServiceInterface;
use App\Domain\Model\Order\DsCitiesRepositoryInterface;
use App\Domain\Model\Order\DsOrderConfirmed;
use App\Domain\Model\Order\DsOrderCreated;
use App\Domain\Model\Order\DsOrderMapping;
use App\Domain\Model\Order\DsOrderMappingRepositoryInterface;
use App\Domain\Model\Order\DsProvider;
use App\Domain\Model\Order\DsProvincesRepositoryInterface;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\DsProduct;
use App\Domain\Model\Product\DsProductUpdated;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Domain\Model\Order\DsCityData;
use App\Infrastructure\Domain\Model\Order\DsProvinceData;
use Psr\Log\LoggerInterface;

final class DsOrderCreatedHandler
{
    public function __construct(
        private readonly DropshipperServiceInterface $dropshipperService,
        private readonly AeProductImportProductRepositoryInterface $importProductRepository,
        private readonly DsOrderMappingRepositoryInterface $dsOrderMappingRepository,
        private readonly EventBusInterface $eventBus,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly LoggerInterface $logger,
        private readonly LanguageServiceInterface $languageService,
        private readonly CountryServiceInterface $countryService,
        private readonly TranslatorInterface $translator,
        private readonly DsProvincesRepositoryInterface $provincesRepository,
        private readonly DsCitiesRepositoryInterface $citiesRepository,
    ) {
    }

    public function __invoke(DsOrderCreated $event): void
    {
        if ($event->getDsProvider() === DsProvider::AliExpress->value) {
            $this->handleCreateAliExpressOrder($event);
        }
    }

    private function handleCreateAliExpressOrder(DsOrderCreated $event): void
    {
        $order = $event->getOrder();
        $dsProvider = $event->getDsProvider();
        $tenantId = $event->getTenantId();

        $tenant = $this->tenantRepository->findOneById($tenantId);

        if (null === $tenant) {
            $this->logError('Tenant not found', $event);

            return;
        }

        $shippingAddress = $order->getShippingAddress();
        $orderProducts = $order->getOrderProducts();
        $payload = $this->constructPayload($shippingAddress, $orderProducts, $event);

        if (empty($payload['product_items'])) {
            $this->logError('Empty product items', $event, $payload);

            return;
        }

        $orderIds = $this->dropshipperService->createOrder($payload);

        if (null === $orderIds) {
            $this->logError('AliExpress Create Order Request Failed.', $event);

            return;
        }

        $this->saveOrders($order->getOrderId(), $orderIds);

        $this->eventBus->publish(new DsOrderConfirmed(
            $tenantId,
            $dsProvider,
            $order->getOrderId()
        ));

        // Update product stock and cost
        $this->updateProductStockAndCost($tenant, $shippingAddress, $orderProducts, $event);
    }

    private function saveOrders(string $nbOrderId, array $orderIds): void
    {
        foreach ($orderIds as $orderId) {
            $dsOrderMapping = new DsOrderMapping(
                id: $this->dsOrderMappingRepository->findNextId(),
                nbOrderId: $nbOrderId,
                dsOrderId: (string) $orderId,
                dsProvider: DsProvider::AliExpress->value,
            );

            $this->dsOrderMappingRepository->save($dsOrderMapping);
        }
    }

    private function updateProductStockAndCost(Tenant $tenant, array $shippingAddress, array $orderProducts, DsOrderCreated $event): void
    {
        foreach ($orderProducts as $product) {
            $nbProductId = $product['productId'];
            $aeProduct = $this->importProductRepository->findOneByNbProductId($nbProductId);

            if (null === $aeProduct) {
                $this->logError('AeProduct not found.', $event, ['nbProductId' => $nbProductId]);
                continue;
            }

            $productData = $this->dropshipperService->getProduct(
                shipToCountry: $this->countryService->convertThreeToTwoLetterCountryCode($shippingAddress['country']),
                productId: $aeProduct->getAeProductId(),
                targetCurrency: $tenant->getDefaultCurrency(),
                targetLanguage: $tenant->getDefaultLanguage() ?? $this->languageService::EN
            );

            if (null === $productData) {
                $this->logError('AliExpress Get Product Request Failed.', $event, ['aeProductId' => $aeProduct->getAeProductId()]);
                continue;
            }

            $this->triggerDsProductUpdatedEvent($tenant->getId(), $aeProduct->getAeProductId(), $productData, $event);
        }
    }

    private function triggerDsProductUpdatedEvent(string $tenantId, int|string $aeProductId, array $aeProduct, DsOrderCreated $event): void
    {
        $sku_info_dtos = $aeProduct['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? [];

        foreach ($sku_info_dtos as $attribute) {
            $skuId = $attribute['sku_id'];
            $stock = $attribute['sku_available_stock'];
            $cost = $attribute['sku_price'];
            $currencyCode = $attribute['currency_code'];

            $nbProduct = $this->importProductRepository->findOneByAeProductIdAndAeSkuId($aeProductId, (int) $skuId);

            if (null === $nbProduct) {
                $this->logError('Product Import findOneByAeProductIdAndAeSkuId is null.', $event, ['aeProductId' => $aeProductId, 'skuId' => $skuId]);
                continue;
            }

            if (null === $nbProduct->getNbProductId()) {
                $this->logError('Product Import NbProductId is null.', $event, ['aeProductId' => $aeProductId, 'skuId' => $skuId]);
                continue;
            }

            $product = new DsProduct(
                productId: $nbProduct->getNbProductId(),
                stock: (int) $stock,
                cost: NumberHelper::floatToInt(floatval($cost)),
                currencyCode: $currencyCode
            );

            $this->eventBus->publish(new DsProductUpdated(
                $tenantId,
                DsProvider::AliExpress->value,
                $product
            ));
        }
    }

    private function constructPayload(
        array $shippingAddress,
        array $orderProducts,
        DsOrderCreated $event
    ): array {
        $countryCode = $this->countryService->convertThreeToTwoLetterCountryCode($shippingAddress['country']);

        // Get validated province and city from AliExpress
        $validatedAddress = $this->validateAddressForAliExpress(
            $countryCode,
            $shippingAddress['province'] ?? $shippingAddress['city'],
            $shippingAddress['city']
        );

        $postData = [
            'logistics_address' => [
                'address' => $shippingAddress['address'],
                'address2' => $shippingAddress['addressAdditions'],
                'province' => $validatedAddress['province'],
                'city' => $validatedAddress['city'],
                'contact_person' => "{$shippingAddress['firstName']} {$shippingAddress['lastName']}",
                'country' => $countryCode,
                'full_name' => "{$shippingAddress['firstName']} {$shippingAddress['lastName']}",
                'locale' => $this->translator->getLocale(),
                'mobile_no' => AeUtil::formatPhoneNumber($shippingAddress['phone']),
                'phone_country' => AeUtil::getPhoneCountryCode($countryCode),
                'zip' => $shippingAddress['postCode'],
                'vat_no' => $shippingAddress['companyVat'],
                'tax_company' => $shippingAddress['companyName'],
            ],
            'product_items' => [],
        ];

        foreach ($orderProducts as $product) {
            $aeProduct = $this->importProductRepository->findOneByNbProductId($product['productId']);

            if (null === $aeProduct) {
                $this->logError('Ae product import not found', $event, ['nbProductId' => $product['productId']]);
                continue;
            }

            $aeProductId = $aeProduct->getAeProductId();
            $aeSkuAttr = $aeProduct->getAeSkuAttr();
            $aeFreightCode = $aeProduct->getAeFreightCode();

            $postData['product_items'][] = [
                'product_id' => $aeProductId,
                'sku_attr' => $aeSkuAttr,
                'logistics_service_name' => $aeFreightCode,
                'product_count' => $product['quantity'],
            ];
        }

        return $postData;
    }

    /**
     * Validates province and city against AliExpress getAddress API
     * Returns validated province and city, using alternatives if originals don't exist
     * Uses Redis caching to store provinces and cities for better performance.
     */
    private function validateAddressForAliExpress(
        string $countryCode,
        ?string $province,
        string $city
    ): array {
        $cachedProvinces = $this->provincesRepository->find($countryCode, $province);
        $cachedCities = $this->citiesRepository->find($countryCode, $city);

        // If cache exists, use it
        if (null !== $cachedProvinces && null !== $cachedCities) {
            return $this->extractValidatedLocationFromCache($cachedProvinces, $cachedCities);
        }

        // Cache miss - fetch from API
        $addressData = $this->dropshipperService->getAddress(
            $countryCode,
            $this->translator->getLocale()
        );

        // If API call fails, use fallback logic
        if (null === $addressData) {
            return [
                'province' => $province,
                'city' => $city,
            ];
        }

        // Parse and cache the address data
        [$provincesData, $citiesData] = $this->parseAndCacheAddressData($addressData, $countryCode);

        // If parsing failed, use fallback
        if (empty($provincesData) && empty($citiesData)) {
            return [
                'province' => $province,
                'city' => $city,
            ];
        }

        return $this->extractValidatedLocationFromCache($provincesData, $citiesData);
    }

    /**
     * Parse address data from API response and cache it in Redis.
     *
     * @return array{0: DsProvinceData[], 1: DsCityData[]}
     */
    private function parseAndCacheAddressData(array $addressData, string $countryCode): array
    {
        $provincesData = [];
        $citiesData = [];

        // Parse the children field which contains a JSON string
        if (!isset($addressData['children']) || !is_string($addressData['children'])) {
            return [$provincesData, $citiesData];
        }

        $children = json_decode($addressData['children'], true);

        if (!is_array($children)) {
            return [$provincesData, $citiesData];
        }

        foreach ($children as $provinceData) {
            // Check if this is a province type
            if (!isset($provinceData['type']) || 'PROVINCE' !== $provinceData['type']) {
                continue;
            }

            $provinceName = $provinceData['name'] ?? null;
            if (!$provinceName) {
                continue;
            }

            // Add province to the list
            $provincesData[] = new DsProvinceData($provinceName, $countryCode);

            // Extract cities from the province's children
            if (!isset($provinceData['children']) || !is_array($provinceData['children'])) {
                continue;
            }

            foreach ($provinceData['children'] as $cityData) {
                if (!isset($cityData['type']) || 'CITY' !== $cityData['type']) {
                    continue;
                }

                $cityName = $cityData['name'] ?? null;
                if ($cityName) {
                    $citiesData[] = new DsCityData($cityName, $countryCode);
                }
            }
        }

        // Cache the parsed data in Redis
        if (!empty($provincesData)) {
            $this->provincesRepository->save($countryCode, $provincesData);
        }

        if (!empty($citiesData)) {
            $this->citiesRepository->save($countryCode, $citiesData);
        }

        return [$provincesData, $citiesData];
    }

    private function extractValidatedLocationFromCache(
        array $cachedProvinces,
        array $cachedCities
    ): array {
        $provinces = array_map(
            static fn (DsProvinceData $province) => trim($province->getProvinceName()),
            $cachedProvinces
        );

        $cities = array_map(
            static fn (DsCityData $city) => trim($city->getCityName()),
            $cachedCities
        );

        return [
            'province' => $provinces[0] ?? 'Other',
            'city' => $cities[0] ?? 'Other',
        ];
    }

    private function logError(string $message, DsOrderCreated $event, array $context = []): void
    {
        $context['event'] = $event;
        $this->logger->error($message, $context);
    }
}
