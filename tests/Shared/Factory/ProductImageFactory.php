<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

final class ProductImageFactory
{
    public const JPG_ID = 'bd4f3865-5061-4b45-906c-562d37ac0751';
    public const PNG_ID = 'bd4f3865-5061-4b45-906c-562d37ac0754';
    public const PRODUCT_ID = 'bd4f3865-5061-4b45-906c-562d37ac0831';
    public const SIZE_JPG_TIGER = 1_600_000; // in bytes
    public const WIDTH_JPG_TIGER = 2_000; // in pixels
    public const HEIGHT_JPG_TIGER = 3_080; // in pixels
    public const IMAGE_JPG_TIGER = 'tiger_2000_3008_160.jpg'; // Valid jpeg image
    public const MIME_TYPE_PNG = 'image/png';
    public const MIME_TYPE_JPEG = 'image/jpeg';
    public const EXT_PNG = 'png';
    public const EXT_JPG = 'jpg';
    public const ORIGINAL_FILENAME_PNG = 'size-chart.png';
    public const SIZE_PNG = 3689; // in bytes
    public const WIDTH_PNG = 1200; // in pixels
    public const HEIGHT_PNG = 800; // in pixels
    public const DATA_PNG = [
        'id' => self::PNG_ID,
        'originalFilename' => self::ORIGINAL_FILENAME_PNG,
        'extension' => self::EXT_PNG,
        'mimeType' => self::MIME_TYPE_PNG,
        'size' => self::SIZE_PNG,
        'width' => self::WIDTH_PNG,
        'height' => self::HEIGHT_PNG,
    ];

    public const DS_PRODUCT_ID = '1005007855523970';
    public const DS_VARIANT_ID = '12000042556247161';
}
