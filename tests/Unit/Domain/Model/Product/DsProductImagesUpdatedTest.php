<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\DsProductImagesUpdated;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Unit\UnitTestCase;

final class DsProductImagesUpdatedTest extends UnitTestCase
{
    public function testGettersCorrectData(): void
    {
        $dsProductImagesUpdated = new DsProductImagesUpdated(
            dsProductId: ProductFactory::DS_PRODUCT_ID,
            dsProvider: ProductFactory::DS_PROVIDER,
            products: [ProductFactory::MACBOOK_ID],
            status: ProductFactory::DS_STATUS_COMPLETED,
        );
        $this->assertSame(ProductFactory::DS_PRODUCT_ID, $dsProductImagesUpdated->getDsProductId());
        $this->assertSame(ProductFactory::DS_PROVIDER, $dsProductImagesUpdated->getDsProvider());
        $this->assertSame([ProductFactory::MACBOOK_ID], $dsProductImagesUpdated->getProducts());
        $this->assertSame(ProductFactory::DS_STATUS_COMPLETED, $dsProductImagesUpdated->getStatus());

        $arr = [
            'dsProductId' => ProductFactory::DS_PRODUCT_ID,
            'dsProvider' => ProductFactory::DS_PROVIDER,
            'products' => [ProductFactory::MACBOOK_ID],
            'status' => ProductFactory::DS_STATUS_COMPLETED,
        ];

        $this->assertSame($arr, $dsProductImagesUpdated->toArray());
    }
}
