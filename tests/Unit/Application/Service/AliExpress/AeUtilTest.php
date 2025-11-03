<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service\AliExpress;

use App\Application\Service\AliExpress\AeUtil;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class AeUtilTest extends UnitTestCase
{
    public function testGetProductIdReturnId(): void
    {
        $this->assertSame(Factory::AE_PRODUCT_ID, AeUtil::getProductId(Factory::AE_PRODUCT_URL));
    }

    public function testGetProductIdReturnNull(): void
    {
        $this->assertNull(AeUtil::getProductId('invalid-product-url'));
    }

    public function testToBase100(): void
    {
        $this->assertSame(100, AeUtil::toBase100('1'));
        $this->assertSame(5, AeUtil::toBase100('0.050'));
        $this->assertSame(1, AeUtil::toBase100('0.01'));
        $this->assertSame(0, AeUtil::toBase100('0'));
    }
}
