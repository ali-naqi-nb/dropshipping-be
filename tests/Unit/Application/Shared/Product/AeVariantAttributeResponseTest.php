<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared\Product;

use App\Application\Shared\Product\AeVariantAttributeResponse;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class AeVariantAttributeResponseTest extends UnitTestCase
{
    public function testFromAeAttribute(): void
    {
        $aeAttribute = Factory::createAeProductImportProductAttribute();
        $response = AeVariantAttributeResponse::fromAeAttribute($aeAttribute);

        $this->assertSame($aeAttribute->getAeAttributeName(), $response->getAeVariantAttributeName());
        $this->assertSame($aeAttribute->getAeAttributeType()->value, $response->getAeVariantAttributeType());
        $this->assertSame($aeAttribute->getAeAttributeValue(), $response->getAeVariantAttributeValue());
    }
}
