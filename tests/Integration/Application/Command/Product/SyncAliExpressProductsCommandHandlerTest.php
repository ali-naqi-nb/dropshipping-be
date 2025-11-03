<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\Product;

use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsCommand;
use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsCommandHandler;
use App\Application\Service\AliExpress\DropshipperServiceInterface;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Log\MainLogRepositoryInterface;
use App\Domain\Model\Log\TenantLogRepositoryInterface;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\DbConfigFactory;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class SyncAliExpressProductsCommandHandlerTest extends IntegrationTestCase
{
    private SyncAliExpressProductsCommandHandler $handler;
    private TenantRepositoryInterface&MockObject $tenantRepository;
    private AeProductImportProductRepositoryInterface&MockObject $importProductRepository;
    private DropshipperServiceInterface&MockObject $dropshipperService;
    private TenantStorageInterface&MockObject $tenantStorage;
    private LoggerInterface&MockObject $logger;
    private EventBusInterface&MockObject $eventBus;
    private MainLogRepositoryInterface&MockObject $mainLogRepository;
    private TenantLogRepositoryInterface&MockObject $tenantLogRepository;
    private TenantServiceInterface&MockObject $tenantService;
    private DoctrineTenantConnection&MockObject $doctrineTenantConnection;

    public function testInvokeWithNonExistentTenant(): void
    {
        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'non-existent-tenant',
            timeoutMinutes: 30
        );

        $this->tenantRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with('non-existent-tenant')
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tenant not found: non-existent-tenant');

        ($this->handler)($command);
    }

    public function testInvokeWithTenantWithoutAliExpressApp(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant
            ->method('isAppInstalled')
            ->with(AppId::AliExpress)
            ->willReturn(false);

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $this->tenantRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with('test-tenant-id')
            ->willReturn($tenant);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tenant does not have AliExpress app installed: test-tenant-id');

        ($this->handler)($command);
    }

    public function testInvokeWithTenantWithNoProducts(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant
            ->method('getId')
            ->willReturn('test-tenant-id');
        $tenant
            ->method('isAppInstalled')
            ->with(AppId::AliExpress)
            ->willReturn(true);
        $tenant
            ->method('getDefaultCurrency')
            ->willReturn('USD');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $this->tenantRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with('test-tenant-id')
            ->willReturn($tenant);

        $this->importProductRepository
            ->expects($this->once())
            ->method('findAllDistinctAeProductIds')
            ->willReturn([]);

        $this->tenantStorage
            ->expects($this->once())
            ->method('setId')
            ->with('test-tenant-id');

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSkippedTenants());
        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(0, $result->getTotalProductsProcessed());
    }

    public function testInvokeWithDryRunMode(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant
            ->method('getId')
            ->willReturn('test-tenant-id');
        $tenant
            ->method('isAppInstalled')
            ->with(AppId::AliExpress)
            ->willReturn(true);
        $tenant
            ->method('getDefaultCurrency')
            ->willReturn('USD');

        $command = new SyncAliExpressProductsCommand(
            dryRun: true,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $this->tenantRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with('test-tenant-id')
            ->willReturn($tenant);

        $this->importProductRepository
            ->expects($this->once())
            ->method('findAllDistinctAeProductIds')
            ->willReturn([]);

        $this->tenantStorage
            ->expects($this->once())
            ->method('setId')
            ->with('test-tenant-id');

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info');

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSkippedTenants());
    }

    public function testInvokeWithAllTenantsMode(): void
    {
        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: null, // All tenants
            timeoutMinutes: 30
        );

        $this->tenantRepository
            ->expects($this->once())
            ->method('findTenantsWithAppInstalled')
            ->with(AppId::AliExpress, 0, 100)
            ->willReturn([]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                $this->stringContains('[AE Sync]'),
                $this->anything()
            );

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getTotalTenantsProcessed());
    }

    public function testInvokeLogsCorrectlyForSingleTenant(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant
            ->method('getId')
            ->willReturn('test-tenant-123');
        $tenant
            ->method('isAppInstalled')
            ->with(AppId::AliExpress)
            ->willReturn(true);
        $tenant
            ->method('getDefaultCurrency')
            ->willReturn('EUR');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-123',
            timeoutMinutes: 45
        );

        $this->tenantRepository
            ->expects($this->once())
            ->method('findOneById')
            ->willReturn($tenant);

        $this->importProductRepository
            ->expects($this->once())
            ->method('findAllDistinctAeProductIds')
            ->willReturn([]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info');

        ($this->handler)($command);
    }

    public function testInvokeWithSuccessfulProductSyncStockUpdate(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(10);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');

        $importProduct->expects($this->once())
            ->method('setAeProductStock')
            ->with(50);

        $this->tenantRepository
            ->method('findOneById')
            ->willReturn($tenant);

        $this->importProductRepository
            ->method('findAllDistinctAeProductIds')
            ->willReturn([123456]);

        $this->importProductRepository
            ->method('findAllByAeProductId')
            ->with(123456)
            ->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->importProductRepository
            ->expects($this->once())
            ->method('save')
            ->with($importProduct);

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getSuccessfulProducts());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeWithSuccessfulProductSyncPriceUpdate(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(50);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');

        $importProduct->expects($this->once())
            ->method('setAeOfferSalePrice')
            ->with(2000);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '20.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->importProductRepository->expects($this->once())->method('save')->with($importProduct);

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeWithSuccessfulProductSyncCurrencyUpdate(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('EUR');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(50);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');

        $importProduct->expects($this->once())
            ->method('setAeSkuCurrencyCode')
            ->with('EUR');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'EUR',
                        ],
                    ],
                ],
            ]);

        $this->importProductRepository->expects($this->once())->method('save')->with($importProduct);

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeWithApiReturningNull(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn(null);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with('[AE Sync] AliExpress API returned null', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getFailedProducts());
    }

    public function testInvokeWithApiThrowingException(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);

        $this->dropshipperService
            ->method('getProduct')
            ->willThrowException(new Exception('API Error'));

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('error')
            ->with('[AE Sync] AliExpress API request failed', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getFailedProducts());
    }

    public function testInvokeWithNoVariantsInDatabase(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with('[AE Sync] No variants found in database', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getFailedProducts());
    }

    public function testInvokeWithEmptySkuDataFromApi(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with('[AE Sync] No SKU data in AliExpress response', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getFailedProducts());
    }

    public function testInvokeWithMissingSkuInApiResponse(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(999);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('debug')
            ->with('[AE Sync] SKU not found in AliExpress data', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getFailedProducts());
        $this->assertSame(1, $result->getVariantsWithErrors());
    }

    public function testInvokeWithMissingRequiredSkuFields(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            // Missing required fields
                        ],
                    ],
                ],
            ]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with('[AE Sync] Missing required SKU fields', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getFailedProducts());
        $this->assertSame(1, $result->getVariantsSkipped());
    }

    public function testInvokeWithInvalidStockValue(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => -5,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with('[AE Sync] Invalid stock value', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getVariantsSkipped());
    }

    public function testInvokeWithInvalidPriceValue(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => -10.00,
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with('[AE Sync] Invalid price value', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getVariantsSkipped());
    }

    public function testInvokeWithNoChanges(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(50);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('debug')
            ->with('[AE Sync] No changes for variant', $this->anything());

        $this->importProductRepository->expects($this->never())->method('save');

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getFailedProducts());
        $this->assertSame(1, $result->getVariantsSkipped());
    }

    public function testInvokeWithMultipleProducts(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct1 = $this->createMock(AeProductImportProduct::class);
        $importProduct1->method('getAeProductId')->willReturn(123456);
        $importProduct1->method('getAeSkuId')->willReturn(789);
        $importProduct1->method('getAeProductStock')->willReturn(10);
        $importProduct1->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct1->method('getAeSkuCurrencyCode')->willReturn('USD');

        $importProduct2 = $this->createMock(AeProductImportProduct::class);
        $importProduct2->method('getAeProductId')->willReturn(654321);
        $importProduct2->method('getAeSkuId')->willReturn(987);
        $importProduct2->method('getAeProductStock')->willReturn(20);
        $importProduct2->method('getAeOfferSalePrice')->willReturn(2000);
        $importProduct2->method('getAeSkuCurrencyCode')->willReturn('USD');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456, 654321]);

        $this->importProductRepository
            ->method('findAllByAeProductId')
            ->willReturnCallback(function ($productId) use ($importProduct1, $importProduct2) {
                return 123456 === $productId ? [$importProduct1] : [$importProduct2];
            });

        $this->dropshipperService
            ->method('getProduct')
            ->willReturnCallback(function ($shipToCountry, $productId) {
                $skuId = 123456 === $productId ? '789' : '987';
                $stock = 123456 === $productId ? 50 : 100;

                return [
                    'ae_item_sku_info_dtos' => [
                        'ae_item_sku_info_d_t_o' => [
                            [
                                'sku_id' => $skuId,
                                'sku_available_stock' => $stock,
                                'offer_sale_price' => '10.00',
                                'currency_code' => 'USD',
                            ],
                        ],
                    ],
                ];
            });

        $this->importProductRepository->expects($this->exactly(2))->method('save');

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(2, $result->getSuccessfulProducts());
        $this->assertSame(2, $result->getVariantsUpdated());
    }

    public function testInvokeWithDryRunDoesNotSave(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: true,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(10);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->importProductRepository->expects($this->never())->method('save');

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeWithAllTenantsAndMultipleChunks(): void
    {
        $tenant1 = $this->createMock(Tenant::class);
        $tenant1->method('getId')->willReturn('tenant-1');

        $tenant2 = $this->createMock(Tenant::class);
        $tenant2->method('getId')->willReturn('tenant-2');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: null,
            timeoutMinutes: 30
        );

        $this->tenantRepository
            ->method('findTenantsWithAppInstalled')
            ->willReturnCallback(function ($appId, $chunk, $size) use ($tenant1, $tenant2) {
                if (0 === $chunk) {
                    return [$tenant1, $tenant2];
                }

                return [];
            });

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info');

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getTotalTenantsProcessed());
    }

    public function testInvokeWithProductSyncFailureIncrementsFailedProducts(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456, 654321]);

        $this->dropshipperService
            ->method('getProduct')
            ->willThrowException(new Exception('API Error'));

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('error');

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(2, $result->getFailedProducts());
    }

    public function testInvokeWithMultipleVariantsForSingleProduct(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $variant1 = $this->createMock(AeProductImportProduct::class);
        $variant1->method('getAeProductId')->willReturn(123456);
        $variant1->method('getAeSkuId')->willReturn(789);
        $variant1->method('getAeProductStock')->willReturn(10);
        $variant1->method('getAeOfferSalePrice')->willReturn(1000);
        $variant1->method('getAeSkuCurrencyCode')->willReturn('USD');

        $variant2 = $this->createMock(AeProductImportProduct::class);
        $variant2->method('getAeProductId')->willReturn(123456);
        $variant2->method('getAeSkuId')->willReturn(790);
        $variant2->method('getAeProductStock')->willReturn(20);
        $variant2->method('getAeOfferSalePrice')->willReturn(1500);
        $variant2->method('getAeSkuCurrencyCode')->willReturn('USD');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$variant1, $variant2]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                        [
                            'sku_id' => '790',
                            'sku_available_stock' => 100,
                            'offer_sale_price' => '15.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->importProductRepository->expects($this->exactly(2))->method('save');

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getSuccessfulProducts());
        $this->assertSame(2, $result->getVariantsUpdated());
    }

    public function testInvokeWithAllChangesStockPriceAndCurrency(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('EUR');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(10);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');

        $importProduct->expects($this->once())->method('setAeProductStock')->with(50);
        $importProduct->expects($this->once())->method('setAeOfferSalePrice')->with(2000);
        $importProduct->expects($this->once())->method('setAeSkuCurrencyCode')->with('EUR');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '20.00',
                            'currency_code' => 'EUR',
                        ],
                    ],
                ],
            ]);

        $this->importProductRepository->expects($this->once())->method('save')->with($importProduct);

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getSuccessfulProducts());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeWithMixedSuccessAndFailureProducts(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct1 = $this->createMock(AeProductImportProduct::class);
        $importProduct1->method('getAeProductId')->willReturn(123456);
        $importProduct1->method('getAeSkuId')->willReturn(789);
        $importProduct1->method('getAeProductStock')->willReturn(10);
        $importProduct1->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct1->method('getAeSkuCurrencyCode')->willReturn('USD');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456, 654321]);

        $this->importProductRepository
            ->method('findAllByAeProductId')
            ->willReturnCallback(function ($productId) use ($importProduct1) {
                return 123456 === $productId ? [$importProduct1] : [];
            });

        $this->dropshipperService
            ->method('getProduct')
            ->willReturnCallback(function ($shipToCountry, $productId) {
                if (123456 === $productId) {
                    return [
                        'ae_item_sku_info_dtos' => [
                            'ae_item_sku_info_d_t_o' => [
                                [
                                    'sku_id' => '789',
                                    'sku_available_stock' => 50,
                                    'offer_sale_price' => '10.00',
                                    'currency_code' => 'USD',
                                ],
                            ],
                        ],
                    ];
                }

                return null;
            });

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getSuccessfulProducts());
        $this->assertSame(1, $result->getFailedProducts());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeWithNonStringStockValue(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 'invalid',
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with('[AE Sync] Invalid stock value', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getVariantsSkipped());
    }

    public function testInvokeWithNonStringPriceValue(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => 'invalid',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with('[AE Sync] Invalid price value', $this->anything());

        $result = ($this->handler)($command);

        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getVariantsSkipped());
    }

    public function testInvokeWithDefaultLanguage(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn(null);

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(10);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->with(
                'US',
                123456,
                'USD',
                'en_US'
            )
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeDispatchesEventWhenProductHasNbProductId(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(10);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');
        $importProduct->method('getNbProductId')->willReturn('550e8400-e29b-41d4-a716-446655440000');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->eventBus
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(function ($event) {
                return $event instanceof \App\Domain\Model\Product\DsProductUpdated
                    && 'test-tenant-id' === $event->getTenantId()
                    && 'aliexpress' === $event->getDsProvider()
                    && '550e8400-e29b-41d4-a716-446655440000' === $event->getProduct()->getProductId()
                    && 50 === $event->getProduct()->getStock()
                    && 1000 === $event->getProduct()->getCost()
                    && 'USD' === $event->getProduct()->getCurrencyCode();
            }));

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeDoesNotDispatchEventWhenProductHasNoNbProductId(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: false,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(10);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');
        $importProduct->method('getNbProductId')->willReturn(null);

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->eventBus
            ->expects($this->never())
            ->method('publish');

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    public function testInvokeDoesNotDispatchEventInDryRunMode(): void
    {
        $tenant = $this->createMock(Tenant::class);
        $tenant->method('getId')->willReturn('test-tenant-id');
        $tenant->method('isAppInstalled')->with(AppId::AliExpress)->willReturn(true);
        $tenant->method('getDefaultCurrency')->willReturn('USD');
        $tenant->method('getDefaultLanguage')->willReturn('en_US');

        $command = new SyncAliExpressProductsCommand(
            dryRun: true,
            tenantId: 'test-tenant-id',
            timeoutMinutes: 30
        );

        $importProduct = $this->createMock(AeProductImportProduct::class);
        $importProduct->method('getAeProductId')->willReturn(123456);
        $importProduct->method('getAeSkuId')->willReturn(789);
        $importProduct->method('getAeProductStock')->willReturn(10);
        $importProduct->method('getAeOfferSalePrice')->willReturn(1000);
        $importProduct->method('getAeSkuCurrencyCode')->willReturn('USD');
        $importProduct->method('getNbProductId')->willReturn('550e8400-e29b-41d4-a716-446655440000');

        $this->tenantRepository->method('findOneById')->willReturn($tenant);
        $this->importProductRepository->method('findAllDistinctAeProductIds')->willReturn([123456]);
        $this->importProductRepository->method('findAllByAeProductId')->with(123456)->willReturn([$importProduct]);

        $this->dropshipperService
            ->method('getProduct')
            ->willReturn([
                'ae_item_sku_info_dtos' => [
                    'ae_item_sku_info_d_t_o' => [
                        [
                            'sku_id' => '789',
                            'sku_available_stock' => 50,
                            'offer_sale_price' => '10.00',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]);

        $this->eventBus
            ->expects($this->never())
            ->method('publish');

        $result = ($this->handler)($command);

        $this->assertSame(1, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getVariantsUpdated());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->importProductRepository = $this->createMock(AeProductImportProductRepositoryInterface::class);
        $this->dropshipperService = $this->createMock(DropshipperServiceInterface::class);
        $this->tenantRepository = $this->createMock(TenantRepositoryInterface::class);
        $this->tenantStorage = $this->createMock(TenantStorageInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);
        $this->mainLogRepository = $this->createMock(MainLogRepositoryInterface::class);
        $this->tenantLogRepository = $this->createMock(TenantLogRepositoryInterface::class);
        $this->tenantService = $this->createMock(TenantServiceInterface::class);
        $this->doctrineTenantConnection = $this->createMock(DoctrineTenantConnection::class);

        // Set up default behavior for tenant database connection
        $this->tenantService
            ->method('getDbConfig')
            ->willReturnCallback(function ($tenantId) {
                return DbConfigFactory::getDbConfig($tenantId);
            });

        $this->doctrineTenantConnection
            ->method('create')
            ->willReturnSelf();

        $this->handler = new SyncAliExpressProductsCommandHandler(
            $this->importProductRepository,
            $this->dropshipperService,
            $this->tenantRepository,
            $this->tenantStorage,
            $this->logger,
            $this->eventBus,
            $this->mainLogRepository,
            $this->tenantLogRepository,
            $this->tenantService,
            $this->doctrineTenantConnection
        );
    }
}
