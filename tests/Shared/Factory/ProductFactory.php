<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

final class ProductFactory
{
    public const DS_PRODUCT_ID = '1005007433426570';
    public const DS_PROVIDER = 'ali-express'; // app name from dropshipping
    public const NOTIFICATION_STATUS = 'success';
    public const PRODUCT_TYPE_ID = 'a01db98a-6ab4-41b3-87ac-48c39df8e96b';
    public const PRODUCT_TYPE_NAME = 'color';
    public const IMAGES = ['https://example.com/image.png'];
    public const PRODUCTS = [
        [
            'dsVariantId' => '1005007445321570',
            'productId' => 'dde469c4-d731-4840-a6d2-7d12dc6b27ea',
            'name' => 'some name 1',
        ],
    ];

    public const WINDOWS_10_PRO_ID = '48ca756b-9210-4ee1-8807-b461238dae40';
    public const WINDOWS_10_PRO_NAME = 'Windows 10 Professional';
    public const DS_VARIANT_ID = 'some-variant-id';
    public const ID = 'f30b838b-f0c2-4022-9e39-1298e9f46b2f';
    public const NAME = 'Test Product';
    public const STOCK = 50;
    public const BARCODE = '5956300311';
    public const WEIGHT = 5;
    public const LENGTH_INT = 5545;
    public const WIDTH_INT = 3323;
    public const HEIGHT_INT = 212;
    public const COST_PER_ITEM = 6;

    public const CATEGORY_ID_FIRST = 'e1759158-5c09-4d11-93e1-255e1dc84abe';
    public const CATEGORY_ID_SECOND = '33f2d631-5c8d-4800-b35c-06cf5516ae36';

    public const DS_STATUS_COMPLETED = 'completed';
    public const MACBOOK_ID = '9ddd448d-6b3b-4756-81de-5f4f88fcdc95';
}
