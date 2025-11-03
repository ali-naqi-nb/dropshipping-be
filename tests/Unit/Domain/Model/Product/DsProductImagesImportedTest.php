<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\DsProductImagesImported;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductImageFactory;
use App\Tests\Unit\UnitTestCase;

final class DsProductImagesImportedTest extends UnitTestCase
{
    public function testDsProductImagesImportedGetters(): void
    {
        $dsProductId = ProductImageFactory::DS_PRODUCT_ID;
        $dsVariantId = ProductImageFactory::DS_VARIANT_ID;
        $dsProvider = DsProviderFactory::ALI_EXPRESS;
        $images = [
            [
                'dsVariantId' => $dsVariantId,
                'images' => [
                    [
                        'id' => ProductImageFactory::JPG_ID,
                        'originalFilename' => ProductImageFactory::IMAGE_JPG_TIGER,
                        'mimeType' => ProductImageFactory::MIME_TYPE_JPEG,
                        'extension' => ProductImageFactory::EXT_JPG,
                        'size' => ProductImageFactory::SIZE_JPG_TIGER,
                        'width' => ProductImageFactory::WIDTH_JPG_TIGER,
                        'height' => ProductImageFactory::HEIGHT_JPG_TIGER,
                        'altText' => '',
                    ],
                ],
            ],
        ];

        $productImagesImported = new DsProductImagesImported(
            dsProductId: $dsProductId,
            dsProvider: $dsProvider,
            products: $images
        );
        $this->assertSame($dsProductId, $productImagesImported->getDsProductId());
        $this->assertSame($dsProvider, $productImagesImported->getDsProvider());
        $this->assertSame($images, $productImagesImported->getProducts());
    }
}
