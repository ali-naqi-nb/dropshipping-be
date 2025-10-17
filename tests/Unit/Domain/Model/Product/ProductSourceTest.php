<?php

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\ProductSource;
use App\Tests\Unit\UnitTestCase;

final class ProductSourceTest extends UnitTestCase
{
    public function testEnumCount(): void
    {
        $this->assertCount(1, ProductSource::cases());
    }

    public function testValuesMethodReturnsArray(): void
    {
        $sources = ProductSource::values();
        $this->assertIsArray($sources);
        $this->assertSame($sources, ['AliExpress']);
    }
}
