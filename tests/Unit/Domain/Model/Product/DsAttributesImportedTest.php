<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\DsAttributesImported;
use App\Tests\Shared\Factory\AttributeFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductTypeFactory;
use App\Tests\Unit\UnitTestCase;

class DsAttributesImportedTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $productTypeId = ProductTypeFactory::ID;
        $dsProductId = 'productId';
        $dsProvider = DsProviderFactory::ALI_EXPRESS;
        $attributes = [AttributeFactory::getAttribute()];
        $status = 'ack';
        $dsAttributeImported = new DsAttributesImported(
            productTypeId: $productTypeId,
            dsProductId: $dsProductId,
            dsProvider: $dsProvider,
            attributes: $attributes,
            status: $status
        );
        $this->assertSame($productTypeId, $dsAttributeImported->getProductTypeId());
        $this->assertSame($dsProductId, $dsAttributeImported->getDsProductId());
        $this->assertSame($dsProvider, $dsAttributeImported->getDsProvider());
        $this->assertSame($attributes, $dsAttributeImported->getAttributes());
        $this->assertSame($status, $dsAttributeImported->getStatus());
    }
}
