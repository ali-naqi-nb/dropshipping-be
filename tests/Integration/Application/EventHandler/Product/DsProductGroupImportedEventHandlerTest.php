<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Product;

use App\Application\EventHandler\Product\DsProductGroupImportedEventHandler;
use App\Application\Service\FileServiceInterface;
use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsProductGroupImported;
use App\Domain\Model\Product\DsProductGroupImportedProduct;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class DsProductGroupImportedEventHandlerTest extends IntegrationTestCase
{
    private DoctrineTenantConnection $connection;
    private FileServiceInterface&MockObject $fileService;
    private AeProductImportRepositoryInterface $productImportRepository;
    private DsProductGroupImportedEventHandler $handler;
    private LoggerInterface&MockObject $logger;
    private AeProductImportProductRepositoryInterface $productImportProductRepository;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        $this->fileService = $this->createMock(FileServiceInterface::class);

        /** @var AeProductImportRepositoryInterface $productImportRepository */
        $productImportRepository = self::getContainer()->get(AeProductImportRepositoryInterface::class);
        $this->productImportRepository = $productImportRepository;

        /** @var AeProductImportProductRepositoryInterface $productImportProductRepository */
        $productImportProductRepository = self::getContainer()->get(AeProductImportProductRepositoryInterface::class);
        $this->productImportProductRepository = $productImportProductRepository;

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new DsProductGroupImportedEventHandler($this->productImportRepository, $this->productImportProductRepository, $this->fileService, $this->logger);
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

    public function testInvokeWorks(): void
    {
        $productsArr = [
            [
                'dsVariantId' => AeProductImportProductFactory::AE_SKU_ID,
                'dsProvider' => DsProviderFactory::ALI_EXPRESS,
                'name' => AeProductImportProductFactory::AE_PRODUCT_NAME,
            ],
        ];

        $rpcResult = [
            'tenantId' => TenantFactory::TENANT_ID,
            'dsProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'dsProvider' => DsProviderFactory::ALI_EXPRESS,
            'products' => $productsArr,
            'status' => 'ack',
        ];

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $completedStep = $aeProductImport->getCompletedStep();

        $this->fileService->expects($this->once())->method('sendDsProductImagesImport')->willReturn($rpcResult);

//        $dsProductGroupImportedProduct = new DsProductGroupImportedProduct(
//            dsVariantId: (string) AeProductImportProductFactory::AE_SKU_ID,
//            productId: ProductFactory::ID,
//            name: ProductFactory::NAME
//        );

        $dsProductGroupImportedProduct = [
            'dsVariantId' => (string) AeProductImportProductFactory::AE_SKU_ID,
            'productId' => ProductFactory::ID,
            'name' => ProductFactory::NAME,
        ];

        $event = new DsProductGroupImported(
            dsProductId: (string) AeProductImportProductFactory::AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            products: [$dsProductGroupImportedProduct]
        );

        $this->handler->__invoke($event);

        /** @var AeProductImportProduct $productImportProduct */
        $productImportProduct = $this->productImportProductRepository
            ->findOneByAeProductIdAndAeSkuId((int) $event->getDsProductId(), AeProductImportProductFactory::AE_SKU_ID);
        $this->assertSame(ProductFactory::ID, $productImportProduct->getNbProductId());

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $this->assertSame($aeProductImport->getCompletedStep(), $completedStep + 1);
    }

    public function testNonExistingAeProductIdLogsError(): void
    {
//        $dsProductGroupImportedProduct = new DsProductGroupImportedProduct(
//            dsVariantId: (string) AeProductImportProductFactory::AE_SKU_ID,
//            productId: ProductFactory::ID,
//            name: ProductFactory::NAME
//        );

        $dsProductGroupImportedProduct = [
            'dsVariantId' => (string) AeProductImportProductFactory::AE_SKU_ID,
            'productId' => ProductFactory::ID,
            'name' => ProductFactory::NAME,
        ];

        $event = new DsProductGroupImported(
            dsProductId: (string) AeProductImportProductFactory::NEW_AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            products: [$dsProductGroupImportedProduct]
        );

        $this->logger->expects($this->once())->method('error');

        $this->handler->__invoke($event);
    }
}
