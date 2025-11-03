<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Product;

use App\Application\EventHandler\Product\UpdateDsProductEventHandler;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\DsProduct;
use App\Domain\Model\Product\UpdateDsProduct;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class UpdateDsProductEventHandlerTest extends IntegrationTestCase
{
    private DoctrineTenantConnection $connection;
    private AeProductImportProductRepositoryInterface $productImportRepository;
    private LoggerInterface&MockObject $logger;
    private UpdateDsProductEventHandler $handler;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        /** @var AeProductImportProductRepositoryInterface $productImportRepository */
        $productImportRepository = self::getContainer()->get(AeProductImportProductRepositoryInterface::class);
        $this->productImportRepository = $productImportRepository;

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new UpdateDsProductEventHandler($this->productImportRepository, $this->logger);
    }

    public function testInvokeUpdatesProductStockAndCost(): void
    {
        $aeProductImport = $this->productImportRepository->findOneByNbProductId(AeProductImportProductFactory::NB_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImportProduct::class, $aeProductImport);

        $oldStock = $aeProductImport->getAeProductStock();
        $oldCost = $aeProductImport->getAeOfferSalePrice();

        $newStock = 150;
        $newCost = 2500;

        $product = new DsProduct(
            productId: AeProductImportProductFactory::NB_PRODUCT_ID,
            stock: $newStock,
            cost: $newCost,
            currencyCode: AeProductImportProductFactory::AE_SKU_CURRENCY_CODE
        );

        $event = new UpdateDsProduct(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            product: $product
        );

        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                [
                    'Received UpdateDsProduct event',
                    $this->callback(function ($context) use ($newStock, $newCost) {
                        return AeProductImportProductFactory::NB_PRODUCT_ID === $context['productId']
                            && $context['stock'] === $newStock
                            && $context['cost'] === $newCost
                            && TenantFactory::TENANT_ID === $context['tenantId']
                            && DsProviderFactory::ALI_EXPRESS === $context['dsProvider'];
                    }),
                ],
                [
                    'Successfully updated product from UpdateDsProduct event',
                    $this->callback(function ($context) use ($newStock, $newCost) {
                        return AeProductImportProductFactory::NB_PRODUCT_ID === $context['productId']
                            && $context['newStock'] === $newStock
                            && $context['newCost'] === $newCost;
                    }),
                ]
            );

        $this->handler->__invoke($event);

        $aeProductImport = $this->productImportRepository->findOneByNbProductId(AeProductImportProductFactory::NB_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImportProduct::class, $aeProductImport);
        $this->assertSame($newStock, $aeProductImport->getAeProductStock());
        $this->assertSame($newCost, $aeProductImport->getAeOfferSalePrice());
        $this->assertNotSame($oldStock, $aeProductImport->getAeProductStock());
        $this->assertNotSame($oldCost, $aeProductImport->getAeOfferSalePrice());
    }

    public function testInvokeWithZeroStockAndCost(): void
    {
        $aeProductImport = $this->productImportRepository->findOneByNbProductId(AeProductImportProductFactory::NB_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImportProduct::class, $aeProductImport);

        $newStock = 0;
        $newCost = 0;

        $product = new DsProduct(
            productId: AeProductImportProductFactory::NB_PRODUCT_ID,
            stock: $newStock,
            cost: $newCost,
            currencyCode: AeProductImportProductFactory::AE_SKU_CURRENCY_CODE
        );

        $event = new UpdateDsProduct(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            product: $product
        );

        $this->logger->expects($this->exactly(2))->method('info');

        $this->handler->__invoke($event);

        $aeProductImport = $this->productImportRepository->findOneByNbProductId(AeProductImportProductFactory::NB_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImportProduct::class, $aeProductImport);
        $this->assertSame(0, $aeProductImport->getAeProductStock());
        $this->assertSame(0, $aeProductImport->getAeOfferSalePrice());
    }

    public function testNonExistingProductIdLogsWarning(): void
    {
        $product = new DsProduct(
            productId: AeProductImportProductFactory::NON_EXISTING_ID,
            stock: 100,
            cost: 5000,
            currencyCode: AeProductImportProductFactory::AE_SKU_CURRENCY_CODE
        );

        $event = new UpdateDsProduct(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            product: $product
        );

        $this->logger->expects($this->once())->method('info');
        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                'Product import not found for product update',
                $this->callback(function ($context) {
                    return AeProductImportProductFactory::NON_EXISTING_ID === $context['productId']
                        && TenantFactory::TENANT_ID === $context['tenantId'];
                })
            );

        $this->handler->__invoke($event);

        // Verify that no product was updated
        $aeProductImport = $this->productImportRepository->findOneByNbProductId(AeProductImportProductFactory::NON_EXISTING_ID);
        $this->assertNull($aeProductImport);
    }

    public function testInvokeWithDifferentCurrency(): void
    {
        $aeProductImport = $this->productImportRepository->findOneByNbProductId(AeProductImportProductFactory::NB_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImportProduct::class, $aeProductImport);

        $newStock = 200;
        $newCost = 3000;
        $newCurrency = 'EUR';

        $product = new DsProduct(
            productId: AeProductImportProductFactory::NB_PRODUCT_ID,
            stock: $newStock,
            cost: $newCost,
            currencyCode: $newCurrency
        );

        $event = new UpdateDsProduct(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            product: $product
        );

        $this->logger->expects($this->exactly(2))->method('info');

        $this->handler->__invoke($event);

        $aeProductImport = $this->productImportRepository->findOneByNbProductId(AeProductImportProductFactory::NB_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImportProduct::class, $aeProductImport);
        $this->assertSame($newStock, $aeProductImport->getAeProductStock());
        $this->assertSame($newCost, $aeProductImport->getAeOfferSalePrice());
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }
}
