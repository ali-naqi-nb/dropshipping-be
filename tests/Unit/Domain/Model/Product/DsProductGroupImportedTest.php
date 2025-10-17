<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\DsProductGroupImported;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Unit\UnitTestCase;

final class DsProductGroupImportedTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $dsProductGroupImportedProduct = [
            'dsVariantId' => (string) AeProductImportProductFactory::AE_SKU_ID,
            'productId' => ProductFactory::ID,
            'name' => ProductFactory::NAME,
        ];

        $dsProductGroupImported = new DsProductGroupImported(
            dsProductId: (string) AeProductImportFactory::AE_PRODUCT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            products: [$dsProductGroupImportedProduct]
        );

        $this->assertSame((string) AeProductImportFactory::AE_PRODUCT_ID, $dsProductGroupImported->getDsProductId());
        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $dsProductGroupImported->getDsProvider());
        $this->assertSame($dsProductGroupImportedProduct, $dsProductGroupImported->getProducts()[0]);
    }
}
