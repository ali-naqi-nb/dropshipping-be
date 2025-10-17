<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Rpc\V1\Product;

use App\Domain\Model\Product\DsProductImagesImported;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Tests\Functional\RpcFunctionalTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductImageFactory;
use Doctrine\DBAL\Exception;

final class DsProductImagesImportedActionTest extends RpcFunctionalTestCase
{
    protected const COMMAND = 'dsProductImagesImported';

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

        $event = new DsProductImagesImported(
            dsProductId: AeProductImportProductFactory::AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            products: [
                [
                    'dsVariantId' => AeProductImportProductFactory::AE_SKU_ID,
                    'images' => [$image],
                ],
            ]
        );

        $response = $this->call([$event]);

        $this->assertSame(RpcResultStatus::SUCCESS, $response->getStatus());
        $result = $response->getResult();

        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $result['dsProvider']);
        $this->assertSame(AeProductImportProductFactory::AE_PRODUCT_ID, $result['dsProductId']);
        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $result['dsProvider']);
    }
}
