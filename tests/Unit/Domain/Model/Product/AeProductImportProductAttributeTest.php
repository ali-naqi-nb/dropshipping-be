<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Uid\Uuid;

final class AeProductImportProductAttributeTest extends UnitTestCase
{
    public function testConstructor(): void
    {
        $importProduct = Factory::createAeProductImportProduct();
        $importProductAttribute = Factory::createAeProductImportProductAttribute(aeProductImportProduct: $importProduct);

        $this->assertInstanceOf(Uuid::class, $importProductAttribute->getId());
        $this->assertSame($importProduct, $importProductAttribute->getAeProductImportProduct());
        $this->assertSame(Factory::AE_ATTRIBUTE_TYPE, $importProductAttribute->getAeAttributeType());
        $this->assertSame(Factory::AE_ATTRIBUTE_NAME, $importProductAttribute->getAeAttributeName());
        $this->assertSame(Factory::AE_ATTRIBUTE_VALUE, $importProductAttribute->getAeAttributeValue());
    }
}
