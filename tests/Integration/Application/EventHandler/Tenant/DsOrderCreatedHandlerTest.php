<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Tenant;

use App\Application\EventHandler\Order\DsOrderCreatedHandler;
use App\Application\Service\AliExpress\DropshipperServiceInterface;
use App\Application\Service\Country\CountryServiceInterface;
use App\Application\Service\TranslatorInterface;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Language\LanguageServiceInterface;
use App\Domain\Model\Order\DsOrderCreated;
use App\Domain\Model\Order\DsOrderCreatedData;
use App\Domain\Model\Order\DsOrderMapping;
use App\Domain\Model\Order\DsOrderMappingRepositoryInterface;
use App\Domain\Model\Order\DsProvider;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeOrderFactory;
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
        $language = $this->createMock(LanguageServiceInterface::class);
        $country = $this->createMock(CountryServiceInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);

        $this->handler = new DsOrderCreatedHandler(
            $this->dropshipperService,
            $this->importProductRepository,
            $this->dsOrderMappingRepository,
            $this->eventBus,
            $this->tenantRepository,
            $this->logger,
            $language,
            $country,
            $translator
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

        $this->dropshipperService->method('createOrder')->willReturn(['order-id-1', 'order-id-2']);
        $this->dsOrderMappingRepository->method('findNextId')->willReturn('017f22e6-79b0-7cc7-98b6-4e0d1d93e378');

        $aeProduct = $this->createMock(AeProductImportProduct::class);
        $this->importProductRepository->method('findOneByNbProductId')->willReturn($aeProduct);
        $this->importProductRepository->method('findOneByAeProductIdAndAeSkuId')->willReturn($aeProduct);
        $aeProduct->method('getAeProductId')->willReturn(11);
        $aeProduct->method('getNbProductId')->willReturn('product-id');

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

        $this->dropshipperService->method('createOrder')->willReturn(['order-id-1', 'order-id-2']);
        $this->dsOrderMappingRepository->method('findNextId')->willReturn('017f22e6-79b0-7cc7-98b6-4e0d1d93e378');

        $aeProduct = $this->createMock(AeProductImportProduct::class);
        $this->importProductRepository->method('findOneByNbProductId')->willReturn($aeProduct);
        $this->importProductRepository->method('findOneByAeProductIdAndAeSkuId')->willReturn($aeProduct);
        $aeProduct->method('getAeProductId')->willReturn(11);
        $aeProduct->method('getNbProductId')->willReturn('product-id');

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
}
