<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Rpc\V1\Product;

use App\Domain\Model\Product\DsProductImagesUpdated;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Tests\Functional\RpcFunctionalTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Shared\Factory\ProductImageFactory;
use Doctrine\DBAL\Exception;

final class DsProductImagesUpdatedActionTest extends RpcFunctionalTestCase
{
    protected const COMMAND = 'dsProductImagesUpdated';

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
        $image = array_merge(ProductImageFactory::DATA_PNG, ['altText' => 'Product Image']);

        $event = new DsProductImagesUpdated(
            AeProductImportProductFactory::AE_PRODUCT_ID,
            DsProviderFactory::ALI_EXPRESS,
            [
                [
                    'productId' => ProductFactory::DS_PRODUCT_ID,
                    'images' => [$image],
                ],
            ],
            'ACK'
        );

        $response = $this->call([$event]);

        $this->assertSame(RpcResultStatus::SUCCESS, $response->getStatus());
        $result = $response->getResult();

        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $result['dsProvider']);
        $this->assertSame(AeProductImportProductFactory::AE_PRODUCT_ID, $result['dsProductId']);
        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $result['dsProvider']);
    }
}
