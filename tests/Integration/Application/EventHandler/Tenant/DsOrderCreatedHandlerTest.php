<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Tenant;

use App\Application\EventHandler\Order\DsOrderCreatedHandler;
use App\Application\Service\AliExpress\DropshipperServiceInterface;
use App\Application\Service\Country\CountryServiceInterface;
use App\Application\Service\TranslatorInterface;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Language\LanguageServiceInterface;
use App\Domain\Model\Order\DsCitiesRepositoryInterface;
use App\Domain\Model\Order\DsOrderCreated;
use App\Domain\Model\Order\DsOrderCreatedData;
use App\Domain\Model\Order\DsOrderMapping;
use App\Domain\Model\Order\DsOrderMappingRepositoryInterface;
use App\Domain\Model\Order\DsProvider;
use App\Domain\Model\Order\DsProvincesRepositoryInterface;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Domain\Model\Order\DsCityData;
use App\Infrastructure\Domain\Model\Order\DsProvinceData;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeOrderFactory;
use App\Tests\Shared\Factory\DsOrderMappingFactory;
use App\Tests\Shared\Factory\LocaleFactory;
use App\Tests\Shared\Factory\OrderFactory;
use App\Tests\Shared\Factory\TenantFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class DsOrderCreatedHandlerTest extends IntegrationTestCase
{
    private MockObject $dropshipperService;
    private MockObject $importProductRepository;
    private MockObject $dsOrderMappingRepository;
    private MockObject $eventBus;
    private MockObject $tenantRepository;
    private MockObject $logger;
    private MockObject $provincesRepository;
    private MockObject $citiesRepository;
    private MockObject $countryService;
    private MockObject $translator;
    private DsOrderCreatedHandler $handler;

    protected function setUp(): void
    {
        parent::setup();

        $this->dropshipperService = $this->createMock(DropshipperServiceInterface::class);
        $this->importProductRepository = $this->createMock(AeProductImportProductRepositoryInterface::class);
        $this->dsOrderMappingRepository = $this->createMock(DsOrderMappingRepositoryInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);
        $this->tenantRepository = $this->createMock(TenantRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->provincesRepository = $this->createMock(DsProvincesRepositoryInterface::class);
        $this->citiesRepository = $this->createMock(DsCitiesRepositoryInterface::class);
        $language = $this->createMock(LanguageServiceInterface::class);
        $this->countryService = $this->createMock(CountryServiceInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->countryService->method('convertThreeToTwoLetterCountryCode')->willReturn('US');
        $this->translator->method('getLocale')->willReturn('en_US');

        $this->handler = new DsOrderCreatedHandler(
            $this->dropshipperService,
            $this->importProductRepository,
            $this->dsOrderMappingRepository,
            $this->eventBus,
            $this->tenantRepository,
            $this->logger,
            $language,
            $this->countryService,
            $this->translator,
            $this->provincesRepository,
            $this->citiesRepository
        );
    }

    public function testOrderCreatedSuccessfully(): void
    {
        $event = $this->createMock(DsOrderCreated::class);
        $event->method('getDsProvider')->willReturn(DsProvider::AliExpress->value);
        $event->method('getTenantId')->willReturn('tenant-id');

        $order = $this->createMock(DsOrderCreatedData::class);
        $event->method('getOrder')->willReturn($order);
        $order->method('getOrderId')->willReturn('61b722bf-17ac-4163-95f0-241938429147');
        $order->method('getShippingAddress')->willReturn(AeOrderFactory::SHIPPING_ADDRESS);
        $order->method('getOrderProducts')->willReturn([['productId' => 'product-id', 'quantity' => 2]]);

        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en');
        $this->tenantRepository->method('findOneById')->willReturn($tenant);

        // Mock cached address data
        $cachedProvinces = [new DsProvinceData('Springfield', 'US')];
        $cachedCities = [new DsCityData('Springfield', 'US')];
        $this->provincesRepository->method('find')->willReturn($cachedProvinces);
        $this->citiesRepository->method('find')->willReturn($cachedCities);

        $this->dropshipperService->method('createOrder')->willReturn(['order-id-1', 'order-id-2']);
        $this->dsOrderMappingRepository->method('findNextId')->willReturn('017f22e6-79b0-7cc7-98b6-4e0d1d93e378');

        $aeProduct = $this->createMock(AeProductImportProduct::class);
        $this->importProductRepository->method('findOneByNbProductId')->willReturn($aeProduct);
        $this->importProductRepository->method('findOneByAeProductIdAndAeSkuId')->willReturn($aeProduct);
        $aeProduct->method('getAeProductId')->willReturn(11);
        $aeProduct->method('getNbProductId')->willReturn('product-id');
        $aeProduct->method('getAeSkuAttr')->willReturn('sku-attr');
        $aeProduct->method('getAeFreightCode')->willReturn('freight-code');

        $this->dropshipperService->method('getProduct')->willReturn(AeOrderFactory::AliExpressGetProductResponse);

        $this->dsOrderMappingRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->with($this->isInstanceOf(DsOrderMapping::class));

        $this->eventBus
            ->expects($this->exactly(3))
            ->method('publish');

        $this->handler->__invoke($event);
    }

    public function testAliExpressProductDataReturnsNullLogsError(): void
    {
        // Arrange
        $event = $this->createMock(DsOrderCreated::class);
        $event->method('getDsProvider')->willReturn(DsProvider::AliExpress->value);
        $event->method('getTenantId')->willReturn('tenant-id');

        $order = $this->createMock(DsOrderCreatedData::class);
        $event->method('getOrder')->willReturn($order);
        $order->method('getOrderId')->willReturn('61b722bf-17ac-4163-95f0-241938429147');
        $order->method('getShippingAddress')->willReturn(AeOrderFactory::SHIPPING_ADDRESS);
        $order->method('getOrderProducts')->willReturn([['productId' => 'product-id', 'quantity' => 2]]);

        $tenant = $this->createMock(Tenant::class);
        $this->tenantRepository->method('findOneById')->willReturn($tenant);

        // Mock cached address data
        $cachedProvinces = [new DsProvinceData('Springfield', 'US')];
        $cachedCities = [new DsCityData('Springfield', 'US')];
        $this->provincesRepository->method('find')->willReturn($cachedProvinces);
        $this->citiesRepository->method('find')->willReturn($cachedCities);

        $this->dropshipperService->method('createOrder')->willReturn(['order-id-1', 'order-id-2']);
        $this->dsOrderMappingRepository->method('findNextId')->willReturn('017f22e6-79b0-7cc7-98b6-4e0d1d93e378');

        $aeProduct = $this->createMock(AeProductImportProduct::class);
        $this->importProductRepository->method('findOneByNbProductId')->willReturn($aeProduct);
        $this->importProductRepository->method('findOneByAeProductIdAndAeSkuId')->willReturn($aeProduct);
        $aeProduct->method('getAeProductId')->willReturn(11);
        $aeProduct->method('getNbProductId')->willReturn('product-id');
        $aeProduct->method('getAeSkuAttr')->willReturn('sku-attr');
        $aeProduct->method('getAeFreightCode')->willReturn('freight-code');

        $this->dropshipperService->method('getProduct')->willReturn(null);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'AliExpress Get Product Request Failed.',
                $this->arrayHasKey('event')
            );

        $this->handler->__invoke($event);
    }

    public function testAeProductImportProductRepositoryReturnsNullForSkuIdLogsError(): void
    {
        // Arrange
        $event = $this->createMock(DsOrderCreated::class);
        $event->method('getDsProvider')->willReturn(DsProvider::AliExpress->value);
        $event->method('getTenantId')->willReturn('tenant-id');

        $order = $this->createMock(DsOrderCreatedData::class);
        $event->method('getOrder')->willReturn($order);
        $order->method('getOrderId')->willReturn('61b722bf-17ac-4163-95f0-241938429147');
        $order->method('getShippingAddress')->willReturn(AeOrderFactory::SHIPPING_ADDRESS);
        $order->method('getOrderProducts')->willReturn([['productId' => 'product-id', 'quantity' => 2]]);

        $tenant = $this->createMock(Tenant::class);
        $this->tenantRepository->method('findOneById')->willReturn($tenant);

        // Mock cached address data
        $cachedProvinces = [new DsProvinceData('Springfield', 'US')];
        $cachedCities = [new DsCityData('Springfield', 'US')];
        $this->provincesRepository->method('find')->willReturn($cachedProvinces);
        $this->citiesRepository->method('find')->willReturn($cachedCities);

        $aeProduct = $this->createMock(AeProductImportProduct::class);
        $aeProduct->method('getAeProductId')->willReturn(123);
        $aeProduct->method('getAeSkuAttr')->willReturn('sku-attr');
        $aeProduct->method('getAeFreightCode')->willReturn('freight-code');

        $this->dropshipperService->method('createOrder')->willReturn(['order-id-1', 'order-id-2']);
        $this->dsOrderMappingRepository->method('findNextId')->willReturn('017f22e6-79b0-7cc7-98b6-4e0d1d93e378');

        $this->importProductRepository
            ->method('findOneByNbProductId')
            ->with('product-id')
            ->willReturn($aeProduct);

        $aeProduct->method('getAeProductId')->willReturn(11);
        $aeProduct->method('getNbProductId')->willReturn('product-id');

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn(AeOrderFactory::AliExpressGetProductResponse);

        $this->importProductRepository
            ->method('findOneByAeProductIdAndAeSkuId')
            ->willReturn(null);

        $this->logger
            ->expects($this->exactly(2))
            ->method('error')
            ->with(
                'Product Import findOneByAeProductIdAndAeSkuId is null.',
                $this->arrayHasKey('aeProductId')
            );

        // Act
        $this->handler->__invoke($event);
    }

    public function testAddressValidationWithCacheHit(): void
    {
        // Arrange
        $event = $this->createMock(DsOrderCreated::class);
        $event->method('getDsProvider')->willReturn(DsProvider::AliExpress->value);
        $event->method('getTenantId')->willReturn(TenantFactory::TENANT_ID);

        $order = $this->createMock(DsOrderCreatedData::class);
        $event->method('getOrder')->willReturn($order);
        $order->method('getOrderId')->willReturn(OrderFactory::NON_EXISTING_ORDER_ID);
        $order->method('getShippingAddress')->willReturn([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => '123 Main St',
            'addressAdditions' => 'Apt 1',
            'city' => 'Lagos',
            'province' => 'Lagos State',
            'country' => 'NGA',
            'phone' => '+234123456789',
            'postCode' => '100001',
            'companyVat' => '',
            'companyName' => '',
        ]);
        $order->method('getOrderProducts')->willReturn([['productId' => 'product-id', 'quantity' => 1]]);

        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getDefaultCurrency')->willReturn(TenantFactory::SECOND_CURRENCY);
        $tenant->method('getDefaultLanguage')->willReturn(LocaleFactory::EN);
        $this->tenantRepository->method('findOneById')->willReturn($tenant);

        $cachedProvinces = [
            new DsProvinceData('Lagos State', 'NG'),
            new DsProvinceData('Rivers State', 'NG'),
        ]
        ;
        $cachedCities = [
            new DsCityData('Lagos', 'NG'),
            new DsCityData('Port Harcourt', 'NG'),
        ];

        $this->provincesRepository->expects($this->once())
            ->method('find')
            ->with('US', 'Lagos State')
            ->willReturn($cachedProvinces);

        $this->citiesRepository->expects($this->once())
            ->method('find')
            ->with('US', 'Lagos')
            ->willReturn($cachedCities);

        // Should not call getAddress API since cache exists
        $this->dropshipperService->expects($this->never())->method('getAddress');

        $aeProduct = $this->createMock(AeProductImportProduct::class);
        $aeProduct->method('getAeProductId')->willReturn(123);
        $aeProduct->method('getAeSkuAttr')->willReturn('sku-attr');
        $aeProduct->method('getAeFreightCode')->willReturn('freight-code');
        $aeProduct->method('getNbProductId')->willReturn(DsOrderMappingFactory::NEW_ORDER_NB_ORDER_ID);

        $this->importProductRepository->method('findOneByNbProductId')->willReturn($aeProduct);
        $this->importProductRepository->method('findOneByAeProductIdAndAeSkuId')->willReturn($aeProduct);

        $this->dropshipperService->method('createOrder')->willReturn(['order-id-1']);
        $this->dropshipperService->method('getProduct')->willReturn(AeOrderFactory::AliExpressGetProductResponse);
        $this->dsOrderMappingRepository->method('findNextId')->willReturn('017f22e6-79b0-7cc7-98b6-4e0d1d93e378');

        $this->handler->__invoke($event);

        $this->assertTrue(true);
    }

    public function testAddressValidationWithCacheMiss(): void
    {
        // Arrange
        $event = $this->createMock(DsOrderCreated::class);
        $event->method('getDsProvider')->willReturn(DsProvider::AliExpress->value);
        $event->method('getTenantId')->willReturn('tenant-id');

        $order = $this->createMock(DsOrderCreatedData::class);
        $event->method('getOrder')->willReturn($order);
        $order->method('getOrderId')->willReturn(OrderFactory::NON_EXISTING_ORDER_ID);
        $order->method('getShippingAddress')->willReturn([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => '123 Main St',
            'addressAdditions' => 'Apt 1',
            'city' => 'Lagos',
            'province' => 'Lagos State',
            'country' => 'NGA',
            'phone' => '+234123456789',
            'postCode' => '100001',
            'companyVat' => '',
            'companyName' => '',
        ]);
        $order->method('getOrderProducts')->willReturn([['productId' => 'product-id', 'quantity' => 1]]);

        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en');
        $this->tenantRepository->method('findOneById')->willReturn($tenant);

        // Mock cache miss
        $this->provincesRepository->method('find')->willReturn(null);
        $this->citiesRepository->method('find')->willReturn(null);

        // Mock AliExpress API response
        $addressResponse = [
            'country' => 'NG',
            'children' => json_encode([
                [
                    'name' => 'Lagos State',
                    'type' => 'PROVINCE',
                    'hasChildren' => true,
                    'children' => [
                        ['name' => 'Lagos', 'type' => 'CITY', 'hasChildren' => false],
                        ['name' => 'Ikeja', 'type' => 'CITY', 'hasChildren' => false],
                        ['name' => 'Other', 'type' => 'CITY', 'hasChildren' => false],
                    ],
                ],
                [
                    'name' => 'Rivers State',
                    'type' => 'PROVINCE',
                    'hasChildren' => true,
                    'children' => [
                        ['name' => 'Port Harcourt', 'type' => 'CITY', 'hasChildren' => false],
                        ['name' => 'Other', 'type' => 'CITY', 'hasChildren' => false],
                    ],
                ],
            ]),
        ];

        $this->dropshipperService->expects($this->once())
            ->method('getAddress')
            ->with('US', 'en_US')
            ->willReturn($addressResponse);

        // Should save to cache
        $this->provincesRepository->expects($this->once())
            ->method('save')
            ->with(
                'US',
                $this->callback(function ($provinces) {
                    return 2 === count($provinces)
                        && $provinces[0] instanceof DsProvinceData
                        && 'Lagos State' === $provinces[0]->getProvinceName();
                })
            );

        $this->citiesRepository->expects($this->once())
            ->method('save')
            ->with(
                'US',
                $this->callback(function ($cities) {
                    return 5 === count($cities)
                        && $cities[0] instanceof DsCityData
                        && 'Lagos' === $cities[0]->getCityName();
                })
            );

        $aeProduct = $this->createMock(AeProductImportProduct::class);
        $aeProduct->method('getAeProductId')->willReturn(123);
        $aeProduct->method('getAeSkuAttr')->willReturn('sku-attr');
        $aeProduct->method('getAeFreightCode')->willReturn('freight-code');
        $aeProduct->method('getNbProductId')->willReturn('product-id');

        $this->importProductRepository->method('findOneByNbProductId')->willReturn($aeProduct);
        $this->importProductRepository->method('findOneByAeProductIdAndAeSkuId')->willReturn($aeProduct);

        $this->dropshipperService->method('createOrder')->willReturn(['order-id-1']);
        $this->dropshipperService->method('getProduct')->willReturn(AeOrderFactory::AliExpressGetProductResponse);
        $this->dsOrderMappingRepository->method('findNextId')->willReturn('017f22e6-79b0-7cc7-98b6-4e0d1d93e378');

        $this->handler->__invoke($event);

        $this->assertTrue(true);
    }

    public function testAddressValidationFallsBackToOther(): void
    {
        // Arrange
        $event = $this->createMock(DsOrderCreated::class);
        $event->method('getDsProvider')->willReturn(DsProvider::AliExpress->value);
        $event->method('getTenantId')->willReturn('tenant-id');

        $order = $this->createMock(DsOrderCreatedData::class);
        $event->method('getOrder')->willReturn($order);
        $order->method('getOrderId')->willReturn(OrderFactory::NON_EXISTING_ORDER_ID);
        $order->method('getShippingAddress')->willReturn([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => '123 Main St',
            'addressAdditions' => 'Apt 1',
            'city' => 'InvalidCity',
            'province' => 'InvalidProvince',
            'country' => 'NGA',
            'phone' => '+234123456789',
            'postCode' => '100001',
            'companyVat' => '',
            'companyName' => '',
        ]);
        $order->method('getOrderProducts')->willReturn([['productId' => 'product-id', 'quantity' => 1]]);

        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en');
        $this->tenantRepository->method('findOneById')->willReturn($tenant);

        // Mock cached address data - but with different names
        $cachedProvinces = [
            new DsProvinceData('Lagos State', 'NG'),
            new DsProvinceData('Other', 'NG'),
        ];
        $cachedCities = [
            new DsCityData('Lagos', 'NG'),
            new DsCityData('Other', 'NG'),
        ];

        $this->provincesRepository->method('find')->willReturn($cachedProvinces);
        $this->citiesRepository->method('find')->willReturn($cachedCities);

        $aeProduct = $this->createMock(AeProductImportProduct::class);
        $aeProduct->method('getAeProductId')->willReturn(123);
        $aeProduct->method('getAeSkuAttr')->willReturn('sku-attr');
        $aeProduct->method('getAeFreightCode')->willReturn('freight-code');
        $aeProduct->method('getNbProductId')->willReturn('product-id');

        $this->importProductRepository->method('findOneByNbProductId')->willReturn($aeProduct);
        $this->importProductRepository->method('findOneByAeProductIdAndAeSkuId')->willReturn($aeProduct);

        $this->dropshipperService->method('createOrder')->willReturn(['order-id-1']);
        $this->dropshipperService->method('getProduct')->willReturn(AeOrderFactory::AliExpressGetProductResponse);
        $this->dsOrderMappingRepository->method('findNextId')->willReturn('017f22e6-79b0-7cc7-98b6-4e0d1d93e378');

        $this->handler->__invoke($event);

        $this->assertTrue(true);
    }
}
