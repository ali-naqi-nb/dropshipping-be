<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\DsProductGroupImportedProduct;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Unit\UnitTestCase;

final class DsProductGroupImportedProductTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $dsProductGroupImportedProduct = new DsProductGroupImportedProduct(
            dsVariantId: (string) AeProductImportProductFactory::AE_SKU_ID,
            productId: (string) AeProductImportProductFactory::AE_PRODUCT_ID,
            name: AeProductImportProductFactory::AE_PRODUCT_NAME
        );

        $this->assertSame((string) AeProductImportProductFactory::AE_SKU_ID, $dsProductGroupImportedProduct->getDsVariantId());
        $this->assertSame((string) AeProductImportProductFactory::AE_PRODUCT_ID, $dsProductGroupImportedProduct->getProductId());
        $this->assertSame(AeProductImportProductFactory::AE_PRODUCT_NAME, $dsProductGroupImportedProduct->getName());
    }
}
