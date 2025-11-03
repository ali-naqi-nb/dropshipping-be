<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\DsProductTypeImported;
use App\Tests\Shared\Factory\ProductTypeFactory;
use App\Tests\Unit\UnitTestCase;

final class DsProductTypeImportedTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $productTypeId = ProductTypeFactory::ID;
        $productTypeName = ProductTypeFactory::NAME;
        $dsProductId = 'some id';
        $dsProvider = 'some provider';
        $event = new DsProductTypeImported(
            $productTypeId,
            $productTypeName,
            $dsProductId,
            $dsProvider
        );
        $this->assertSame($productTypeId, $event->getProductTypeId());
        $this->assertSame($productTypeName, $event->getProductTypeName());
        $this->assertSame($dsProductId, $event->getDsProductId());
        $this->assertSame($dsProvider, $event->getDsProvider());
        $this->assertSame(DsProductTypeImported::SUCCESS, $event->getStatus());
    }
}
