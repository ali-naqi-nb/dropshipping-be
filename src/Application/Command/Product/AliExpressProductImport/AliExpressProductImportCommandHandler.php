<?php

declare(strict_types=1);

namespace App\Application\Command\Product\AliExpressProductImport;

use App\Application\Service\AliExpress\AeUtil;
use App\Application\Service\AliExpress\DropshipperServiceInterface;
use App\Application\Service\Product\AeProductImportResponseAssembler;
use App\Application\Service\TranslatorInterface;
use App\Application\Shared\Error\ErrorResponse;
use App\Application\Shared\Product\AeProductImportResponse;
use App\Domain\Model\Product\AeAttributeType;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductAttribute;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\AeProductImportProductValidatorInterface;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use Exception;
use Psr\Log\LoggerInterface;

final class AliExpressProductImportCommandHandler
{
    public function __construct(
        private readonly TenantStorageInterface $tenantStorage,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly AeProductImportProductValidatorInterface $validator,
        private readonly DropshipperServiceInterface $dropshipperService,
        private readonly AeProductImportProductRepositoryInterface $importProductRepository,
        private readonly AeProductImportResponseAssembler $responseAssembler,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(AliExpressProductImportCommand $command): AeProductImportResponse|ErrorResponse
    {
        $errors = $this->validator->validate($command->toArray());
        if ($errors->hasErrors()) {
            return ErrorResponse::fromConstraintViolationList($errors);
        }

        $tenantId = $this->tenantStorage->getId();
        $tenant = null;
        if (null !== $tenantId) {
            $tenant = $this->tenantRepository->findOneById($tenantId);
        }

        if (null === $tenant) {
            return ErrorResponse::notFound('Tenant not found');
        }

        $aeProduct = $this->getAeProduct($tenant->getDefaultCurrency(), $command);
        $categoryId = $aeProduct['ae_item_base_info_dto']['category_id'] ?? null;

        if (null === $aeProduct || null === $categoryId || ($aeCategory = $this->getAeCategory($categoryId)) === null) {
            return ErrorResponse::fromCommonError('Failed to get product information from AliExpress');
        }

        $skuIds = array_map(
            fn (array $attributes) => $attributes['sku_id'],
            $aeProduct['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? []
        );
        $aeShippingOptions = $this->getAeShippingOptions(
            currency: $tenant->getDefaultCurrency(),
            aeProductShipsTo: $command->getAeProductShipsTo(),
            productId: $aeProduct['ae_item_base_info_dto']['product_id'],
            countrySource: $aeProduct['ae_store_info']['store_country_code'],
            skuIds: $skuIds,
        );
        if (null === $aeShippingOptions) {
            return ErrorResponse::fromCommonError('Failed to get product information from AliExpress');
        }

        $importProducts = $this->saveImportData($aeProduct, $aeCategory);

        return $this->responseAssembler->assembleAeProductResponse($importProducts, $aeShippingOptions);
    }

    /**
     * @param array<string, mixed> $aeProduct
     * @param array<string, mixed> $aeCategory
     *
     * @return AeProductImportProduct[]
     */
    private function saveImportData(array $aeProduct, array $aeCategory): array
    {
        $skus = $aeProduct['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? [];
        $base = $aeProduct['ae_item_base_info_dto'];
        $package = $aeProduct['package_info_dto'];

        /** @var AeProductImportProduct[] $imported */
        $imported = [];

        foreach ($skus as $sku) {
            $importProduct = $this->createProduct($base, $sku, $aeCategory, $package);

            $baseAttributes = $this->createBaseAttributes($aeProduct, $importProduct);
            $skuAttributes = $this->createSkuAttributes($sku, $importProduct);
            $importProduct->setAeVariantAttributes(array_merge($skuAttributes, $baseAttributes));

            $secondaryImages = $this->getBaseImages($aeProduct);
            $primaryImages = $this->getSkuImages($sku);
            /** @var array<string, bool> $images */
            $images = [];
            foreach ($secondaryImages as $secondaryImage) {
                $images[$secondaryImage] = false;
            }
            foreach ($primaryImages as $primaryImage) {
                $images[$primaryImage] = true;
            }
            $importProduct->setAeProductImageUrls($images);

            $this->importProductRepository->save($importProduct);
            $imported[] = $importProduct;
        }

        return $imported;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $sku
     * @param array<string, mixed> $category
     * @param array<string, mixed> $package
     */
    private function createProduct(array $base, array $sku, array $category, array $package): AeProductImportProduct
    {
        $importProduct = $this->importProductRepository->findOneByAeProductIdAndAeSkuId($base['product_id'], (int) $sku['sku_id']);

        if (null !== $importProduct) {
            $importProduct->setAeSkuAttr($sku['sku_attr']);
            $importProduct->setAeSkuCode($sku['sku_code'] ?? null);
            $importProduct->setAeProductName($base['subject']);
            $importProduct->setAeProductDescription($base['detail'] ?? null);
            $importProduct->setAeProductCategoryName($category['category_name'] ?? null);
            $importProduct->setAeProductBarcode($sku['barcode'] ?? $sku['ean_code'] ?? null);
            $importProduct->setAeProductWeight(AeUtil::toBase100($package['gross_weight'].''));
            $importProduct->setAeProductLength(AeUtil::toBase100($package['package_length'].''));
            $importProduct->setAeProductWidth(AeUtil::toBase100($package['package_width'].''));
            $importProduct->setAeProductHeight(AeUtil::toBase100($package['package_height'].''));
            $importProduct->setAeProductStock($sku['sku_available_stock']);
            $importProduct->setAeSkuPrice(AeUtil::toBase100($sku['sku_price'].''));
            $importProduct->setAeOfferSalePrice(AeUtil::toBase100($sku['offer_sale_price'].''));
            $importProduct->setAeOfferBulkSalePrice(AeUtil::toBase100($sku['offer_bulk_sale_price'].''));
            $importProduct->setAeSkuCurrencyCode($sku['currency_code']);
        } else {
            $importProduct = new AeProductImportProduct(
                aeProductId: $base['product_id'],
                aeSkuId: $sku['sku_id'],
                aeSkuAttr: $sku['sku_attr'],
                aeSkuCode: $sku['sku_code'] ?? null,
                nbProductId: null,
                aeProductName: $base['subject'],
                aeProductDescription: $base['detail'],
                aeProductCategoryName: $category['category_name'] ?? null,
                aeProductBarcode: $sku['barcode'] ?? $sku['ean_code'] ?? null,
                aeProductWeight: AeUtil::toBase100($package['gross_weight'].''),
                aeProductLength: AeUtil::toBase100($package['package_length'].''),
                aeProductWidth: AeUtil::toBase100($package['package_width'].''),
                aeProductHeight: AeUtil::toBase100($package['package_height'].''),
                aeProductStock: $sku['sku_available_stock'],
                aeSkuPrice: AeUtil::toBase100($sku['sku_price'].''),
                aeOfferSalePrice: AeUtil::toBase100($sku['offer_sale_price'].''),
                aeOfferBulkSalePrice: AeUtil::toBase100($sku['offer_bulk_sale_price'].''),
                aeSkuCurrencyCode: $sku['currency_code'],
                aeFreightCode: null,
                aeShippingFee: null,
                aeShippingFeeCurrency: null,
            );
        }

        return $importProduct;
    }

    /**
     * @param array<string, mixed> $aeProduct
     *
     * @return AeProductImportProductAttribute[]
     */
    private function createBaseAttributes(array $aeProduct, AeProductImportProduct $importProduct): array
    {
        $attributes = $aeProduct['ae_item_properties']['ae_item_property'] ?? [];

        $importProductAttributes = [];
        foreach ($attributes as $attribute) {
            if (str_contains($attribute['attr_value'], DropshipperServiceInterface::AE_INDICATOR_VALUE_NULL)) {
                continue;
            }

            $importProductAttributes[] = new AeProductImportProductAttribute(
                aeProductImportProduct: $importProduct,
                aeAttributeType: AeAttributeType::Attribute,
                aeAttributeName: $attribute['attr_name'],
                aeAttributeValue: $attribute['attr_value'],
            );
        }

        return $importProductAttributes;
    }

    /**
     * @param array<string, mixed> $sku
     *
     * @return AeProductImportProductAttribute[]
     */
    private function createSkuAttributes(array $sku, AeProductImportProduct $importProduct): array
    {
        $attributes = $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'] ?? [];

        $importProductAttributes = [];
        foreach ($attributes as $attribute) {
            $value = $attribute['property_value_definition_name'] ?? $attribute['sku_property_value'];

            if (str_contains($value, DropshipperServiceInterface::AE_INDICATOR_VALUE_NULL)) {
                continue;
            }

            $importProductAttributes[] = new AeProductImportProductAttribute(
                aeProductImportProduct: $importProduct,
                aeAttributeType: AeAttributeType::SkuProperty,
                aeAttributeName: $attribute['sku_property_name'],
                aeAttributeValue: $value,
            );
        }

        return $importProductAttributes;
    }

    /**
     * @param array<string, mixed> $aeProduct
     *
     * @return string[]
     */
    private function getBaseImages(array $aeProduct): array
    {
        $delimitedUrls = $aeProduct['ae_multimedia_info_dto']['image_urls'] ?? '';

        return explode(DropshipperServiceInterface::AE_IMAGES_DELIMITER, $delimitedUrls);
    }

    /**
     * @param array<string, mixed> $sku
     *
     * @return string[]
     */
    private function getSkuImages(array $sku): array
    {
        $attributes = $sku['ae_sku_property_dtos']['ae_sku_property_d_t_o'] ?? [];

        $imageUrls = [];
        foreach ($attributes as $attribute) {
            $image = $attribute['sku_image'] ?? null;

            if (null === $image) {
                continue;
            }

            $imageUrls[] = $image;
        }

        return $imageUrls;
    }

    /**
     * @return ?array<string, mixed>
     */
    private function getAeProduct(string $currency, AliExpressProductImportCommand $command): ?array
    {
        /** @var int $aeProductId */
        $aeProductId = AeUtil::getProductId($command->getAeProductUrl());

        try {
            return $this->dropshipperService->getProduct(
                shipToCountry: $command->getAeProductShipsTo(),
                productId: $aeProductId,
                targetCurrency: $currency,
                targetLanguage: $this->translator->getLocale(),
            );
        } catch (Exception $e) {
            $this->logger->error('Failed fetching AE product details. '.$e->getMessage(), [
                'shipToCountry' => $command->getAeProductShipsTo(),
                'productId' => $aeProductId,
                'targetCurrency' => $currency,
                'targetLanguage' => $this->translator->getLocale(),
            ]);
        }

        return null;
    }

    /**
     * @return ?array<string, mixed>
     */
    private function getAeCategory(int $categoryId): ?array
    {
        try {
            $categories = $this->dropshipperService->getCategory(
                categoryId: $categoryId,
                language: $this->translator->getLocale(),
            );

            return $categories[0] ?? null;
        } catch (Exception $e) {
            $this->logger->error('Failed fetching AE category details. '.$e->getMessage(), [
                'categoryId' => $categoryId,
            ]);
        }

        return null;
    }

    /**
     * @param array<int> $skuIds
     *
     * @return ?array<int, ?array<array<string, mixed>>>
     */
    private function getAeShippingOptions(
        string $currency,
        string $aeProductShipsTo,
        int $productId,
        string $countrySource,
        array $skuIds
    ): ?array {
        try {
            $options = [];

            foreach ($skuIds as $skuId) {
                $aeFreightOptions = $this->dropshipperService->queryFreight(
                    quantity: 1,
                    shipToCountry: $aeProductShipsTo,
                    productId: $productId,
                    language: $this->translator->getLocale(),
                    source: $countrySource,
                    locale: $this->translator->getLocale(),
                    selectedSkuId: "$skuId",
                    currency: $currency,
                );

                $options[$skuId] = $aeFreightOptions;
            }

            return $options;
        } catch (Exception $e) {
            $this->logger->error('Failed fetching AE freight details. '.$e->getMessage(), [
                'shipToCountry' => $aeProductShipsTo,
                'productId' => $productId,
                'language' => $this->translator->getLocale(),
                'source' => $countrySource,
                'locale' => $this->translator->getLocale(),
                'selectedSkuId' => $skuId,
                'currency' => $currency,
            ]);
        }

        return null;
    }
}
