<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Product;

use App\Application\EventHandler\Product\DsProductImagesImportedEventHandler;
use App\Application\Service\ProductServiceInterface;
use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsProductImagesImported;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductImageFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class DsProductImagesImportedEventHandlerTest extends IntegrationTestCase
{
    private DoctrineTenantConnection $connection;
    private ProductServiceInterface&MockObject $productService;
    private AeProductImportRepositoryInterface $productImportRepository;
    private LoggerInterface&MockObject $logger;
    private AeProductImportProductRepositoryInterface $productImportProductRepository;

    private DsProductImagesImportedEventHandler $handler;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        $this->productService = $this->createMock(ProductServiceInterface::class);

        /** @var AeProductImportRepositoryInterface $productImportRepository */
        $productImportRepository = self::getContainer()->get(AeProductImportRepositoryInterface::class);
        $this->productImportRepository = $productImportRepository;

        /** @var AeProductImportProductRepositoryInterface $productImportProductRepository */
        $productImportProductRepository = self::getContainer()->get(AeProductImportProductRepositoryInterface::class);
        $this->productImportProductRepository = $productImportProductRepository;

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new DsProductImagesImportedEventHandler($this->productImportProductRepository, $this->productImportRepository, $this->productService, $this->logger);
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
        $rpcResult = [
            'tenantId' => TenantFactory::TENANT_ID,
            'dsProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'dsProvider' => DsProviderFactory::ALI_EXPRESS,
            'images' => [AeProductImportProductFactory::AE_IMAGE_URL],
            'status' => 'ack',
        ];

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $completedStep = $aeProductImport->getCompletedStep();

        $this->productService->expects($this->once())->method('sendDsProductImagesUpdate')->willReturn($rpcResult);

        $event = new DsProductImagesImported(
            dsProductId: AeProductImportProductFactory::AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            products: [
                [
                    'dsVariantId' => AeProductImportProductFactory::AE_SKU_ID,
                    'images' => [array_merge(ProductImageFactory::DATA_PNG, ['altText' => 'test test'])],
                ],
            ]
        );

        $this->handler->__invoke($event);

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $this->assertSame($aeProductImport->getCompletedStep(), $completedStep + 1);
    }

    public function testNonExistingAeProductIdLogsError(): void
    {
        $event = new DsProductImagesImported(
            dsProductId: AeProductImportProductFactory::NEW_AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            products: [
                [
                    'dsVariantId' => AeProductImportProductFactory::NEW_AE_SKU_ID,
                    'images' => [array_merge(ProductImageFactory::DATA_PNG, ['altText' => 'test test'])],
                ],
            ]
        );

        $this->logger->expects($this->once())->method('error');

        $this->handler->__invoke($event);
    }
}
