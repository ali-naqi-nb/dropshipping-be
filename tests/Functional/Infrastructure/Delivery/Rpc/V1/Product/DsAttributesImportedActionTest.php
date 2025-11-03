<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Rpc\V1\Product;

use App\Domain\Model\Product\DsAttributesImported;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Tests\Functional\RpcFunctionalTestCase;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductFactory;
use Doctrine\DBAL\Exception;

final class DsAttributesImportedActionTest extends RpcFunctionalTestCase
{
    protected const COMMAND = 'dsAttributesImported';

    private DoctrineTenantConnection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();
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

    /**
     * @throws \ReflectionException
     */
    public function testReturnsAck(): void
    {
        $event = new DsAttributesImported(
            ProductFactory::PRODUCT_TYPE_ID,
            (string) AeProductImportProductFactory::AE_PRODUCT_ID,
            DsProviderFactory::ALI_EXPRESS,
            AeProductImportFactory::ATTRIBUTES,
            'ACK'
        );

        $response = $this->call([$event]);

        $this->assertSame(RpcResultStatus::SUCCESS, $response->getStatus());
        $result = $response->getResult();

        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $result['dsProvider']);
        $this->assertSame((string) AeProductImportProductFactory::AE_PRODUCT_ID, $result['dsProductId']);
        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $result['dsProvider']);
    }
}
