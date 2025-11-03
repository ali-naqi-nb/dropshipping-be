<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Product;

use App\Application\EventHandler\Product\DsProductTypeImportedEventHandler;
use App\Application\Service\ProductServiceInterface;
use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsProductTypeImported;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductTypeFactory;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class DsProductTypeImportedEventHandlerTest extends IntegrationTestCase
{
    private DoctrineTenantConnection $connection;

    private ProductServiceInterface&MockObject $productService;
    private AeProductImportRepositoryInterface $productImportRepository;
    private DsProductTypeImportedEventHandler $handler;
    private LoggerInterface&MockObject $logger;

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

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new DsProductTypeImportedEventHandler($this->productService, $this->productImportRepository, $this->logger);
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
            'dsProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'dsProvider' => DsProviderFactory::ALI_EXPRESS,
            'productTypeName' => ProductTypeFactory::NAME,
            'status' => 'ack',
        ];

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $completedStep = $aeProductImport->getCompletedStep();

        $this->productService->expects($this->once())->method('sendDsAttributeImport')->willReturn($rpcResult);

        $event = new DsProductTypeImported(
            productTypeId: ProductTypeFactory::ID,
            productTypeName: ProductTypeFactory::NAME,
            dsProductId: (string) AeProductImportProductFactory::AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
        );

        $this->handler->__invoke($event);

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $this->assertSame($aeProductImport->getCompletedStep(), $completedStep + 1);
    }
}
