<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Product;

use App\Application\EventHandler\Product\DsAttributesImportedEventHandler;
use App\Application\Service\ProductServiceInterface;
use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsAttributesImported;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\AttributeFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductTypeFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class DsAttributesImportedEventHandlerTest extends IntegrationTestCase
{
    private DoctrineTenantConnection $connection;
    private ProductServiceInterface&MockObject $productService;
    private AeProductImportRepositoryInterface $productImportRepository;
    private LoggerInterface&MockObject $logger;

    private DsAttributesImportedEventHandler $handler;

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

        $this->handler = new DsAttributesImportedEventHandler($this->productService, $this->productImportRepository, $this->logger);
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
            'attributes' => AeProductImportFactory::ATTRIBUTES,
            'status' => 'ack',
        ];

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $completedStep = $aeProductImport->getCompletedStep();

        $this->productService->expects($this->once())->method('sendDsProductGroupImport')->willReturn($rpcResult);

        $event = new DsAttributesImported(
            ProductTypeFactory::ID,
            (string) AeProductImportProductFactory::AE_PRODUCT_ID,
            DsProviderFactory::ALI_EXPRESS,
            [AttributeFactory::getAttribute()],
            'ACK'
        );

        $this->handler->__invoke($event);

        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);
        $this->assertSame($aeProductImport->getCompletedStep(), $completedStep + 1);
    }

    public function testNonExistingAeProductIdLogsError(): void
    {
        $event = new DsAttributesImported(
            ProductTypeFactory::ID,
            (string) AeProductImportProductFactory::NEW_AE_PRODUCT_ID,
            DsProviderFactory::ALI_EXPRESS,
            [AttributeFactory::getAttribute()],
            'ACK'
        );

        $this->logger->expects($this->once())->method('error');

        $this->handler->__invoke($event);
    }
}
