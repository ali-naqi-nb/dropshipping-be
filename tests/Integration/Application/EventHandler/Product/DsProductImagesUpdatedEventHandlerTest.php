<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Product;

use App\Application\EventHandler\Product\DsProductImagesUpdatedEventHandler;
use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsProductImagesUpdated;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductFactory;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class DsProductImagesUpdatedEventHandlerTest extends IntegrationTestCase
{
    private DoctrineTenantConnection $connection;
    private AeProductImportRepositoryInterface $productImportRepository;
    private LoggerInterface&MockObject $logger;
    private DsProductImagesUpdatedEventHandler $handler;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        /** @var AeProductImportRepositoryInterface $productImportRepository */
        $productImportRepository = self::getContainer()->get(AeProductImportRepositoryInterface::class);
        $this->productImportRepository = $productImportRepository;

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new DsProductImagesUpdatedEventHandler($this->productImportRepository, $this->logger);
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
        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $completedStep = $aeProductImport->getCompletedStep();

        $event = new DsProductImagesUpdated(
            dsProductId: AeProductImportProductFactory::AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            products: [ProductFactory::ID],
            status: 'ACK'
        );

        $this->handler->__invoke($event);

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $this->assertSame($aeProductImport->getCompletedStep(), $completedStep + 1);
    }

    public function testNonExistingAeProductIdLogsError(): void
    {
        $event = new DsProductImagesUpdated(
            dsProductId: AeProductImportProductFactory::NEW_AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            products: [ProductFactory::ID],
            status: 'ACK'
        );

        $this->logger->expects($this->once())->method('error');

        $this->handler->__invoke($event);
    }
}
