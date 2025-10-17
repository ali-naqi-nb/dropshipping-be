<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared\Product;

use App\Application\Shared\Product\DsProductImagesUpdateResponse;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Unit\UnitTestCase;

class DsProductImagesUpdateResponseTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $dsProvider = ProductFactory::DS_PROVIDER;
        $dsProductId = ProductFactory::DS_PRODUCT_ID;
        $products = ProductFactory::PRODUCTS;
        $status = ProductFactory::NOTIFICATION_STATUS;

        $response = new DsProductImagesUpdateResponse(
            dsProvider: $dsProvider,
            dsProductId: $dsProductId,
            products: $products,
            status: $status
        );

        $this->assertSame($dsProvider, $response->getDsProvider());
        $this->assertSame($dsProductId, $response->getDsProductId());
        $this->assertSame($products, $response->getProducts());
        $this->assertSame($status, $response->getStatus());
    }
}
