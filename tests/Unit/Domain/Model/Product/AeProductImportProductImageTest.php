<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Uid\Uuid;

final class AeProductImportProductImageTest extends UnitTestCase
{
    public function testConstructor(): void
    {
        $importProduct = Factory::createAeProductImportProduct();
        $importProductImage = Factory::createAeProductImportProductImage(aeProductImportProduct: $importProduct);

        $this->assertInstanceOf(Uuid::class, $importProductImage->getId());
        $this->assertSame($importProduct, $importProductImage->getAeProductImportProduct());
        $this->assertSame(Factory::AE_IMAGE_URL, $importProductImage->getAeImageUrl());
    }
}
