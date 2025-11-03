<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Product\AeProductImport;

final class AeProductImportFactory
{
    public const IMPORT_ID = '5c1b2dac-f45c-473c-8ee6-887145c7ef60';
    public const NEW_IMPORT_ID = '204b3443-99d7-492c-b53c-4f3d55531031';
    public const NON_EXISTING_IMPORT_ID = '78728366-46b8-4cbb-bff6-fbbf25aa96d2';
    public const COMPLETED_IMPORT_ID = '44d724b8-08da-4f0c-bed4-9f99dec9d5bf';
    public const AE_PRODUCT_ID = 1005007433426570;
    public const AE_SKU_ID = 12000042556247161;
    public const IMPORT_COMPLETED_STEP = 3;
    public const IMPORT_TOTAL_STEPS = 5;
    public const AE_DELIVERY_CODE = 'CAINIAO_FULFILLMENT_STD';
    public const AE_DELIVERY_SHIP_FROM = 'CN';
    public const AE_DELIVERY_MIN_DELIVERY_DAYS = 12;
    public const AE_DELIVERY_MAX_DELIVERY_DAYS = 19;
    public const AE_DELIVERY_SHIPPING_FEE_CURRENCY = CurrencyFactory::USD;
    public const AE_DELIVERY_SHIPPING_FEE = 1_99;

    public const ATTRIBUTES = [
        [
            'name' => 'color',
            'type' => 'dropdown',
            'value' => 'blue',
        ],
    ];
    public const RESPONSE_IMPORT_PATTERN = [
        'id' => '@string@',
        'aeProductId' => '@null@',
        'progressStep' => '@integer@',
        'totalSteps' => '@integer@',
    ];
    public const PROGRESS_RESPONSE_IMPORT_PATTERN = [
        'id' => '@string@',
        'aeProductId' => '@integer@',
        'progressStep' => '@integer@',
        'totalSteps' => '@integer@',
    ];

    public const GROUP_DATA = [
        [
            'aeProductId' => 1005007802842965,
            'aeSkuId' => 12000042254327371,
            'name' => "Men's shoes 2024 summer new breathable white shoes men's trendy and versatile thick soled sports board shoes trendy shoes",
            'description' => 'Comfortable and stylish footwear for everyday use.',  // Example random lorem sentence
            'sku' => 'Sporty',  // Example random adjective
            'price' => 79,  // Example random integer
            'mainCategoryId' => 12,  // Example category ID
            'additionalCategories' => [],
            'barcode' => 'BARCODE12345',
            'weight' => 18,
            'length' => 16,
            'width' => 24,
            'height' => 10,
            'costPerItem' => 35,
            'productTypeName' => 'Casual',  // Example random adjective
            'attributes' => [
                [
                    'name' => 'Color',
                    'type' => 'sku_property',
                    'value' => 'Blue',
                ],
            ],
            'images' => [
                'https://ae01.alicdn.com/kf/S6066a1930d2a44f29298ff116eea75ecB.jpg',
                'https://ae01.alicdn.com/kf/S78554512fc4b45ea8102c36fa7136404e.jpg',
                'https://ae01.alicdn.com/kf/Sbfce05ca6f6141208e7002a36a67b224j.jpg',
                'https://ae01.alicdn.com/kf/S2a7d777ff9ac4ebb803ec4fcacd030f0F.jpg',
                'https://ae01.alicdn.com/kf/S53f3577381e14db1a483b3fbd1ffd9efT.jpg',
                'https://ae01.alicdn.com/kf/S4e8d282c16e04892835f9f5fb589f934q.jpg',
            ],
            'stock' => 50,  // Example random integer
        ],
        [
            'aeProductId' => 1005007802842965,
            'aeSkuId' => 12000042254327370,
            'name' => "Men's shoes 2024 summer new breathable white shoes men's trendy and versatile thick soled sports board shoes trendy shoes",
            'description' => 'A modern look with a classic feel, perfect for any casual outing.',  // Example random lorem sentence
            'sku' => 'Classic',  // Example random adjective
            'price' => 65,  // Example random integer
            'mainCategoryId' => 12,  // Example category ID
            'additionalCategories' => [],
            'barcode' => 'BARCODE67890',
            'weight' => 2,
            'length' => 16,
            'width' => 24,
            'height' => 10,
            'costPerItem' => 29,
            'productTypeName' => 'Trendy',  // Example random adjective
            'attributes' => [
                [
                    'name' => 'Color',
                    'type' => 'material',  // Example random word
                    'value' => 'Red',
                ],
            ],
            'images' => [
                'https://ae01.alicdn.com/kf/S6066a1930d2a44f29298ff116eea75ecB.jpg',
                'https://ae01.alicdn.com/kf/S78554512fc4b45ea8102c36fa7136404e.jpg',
                'https://ae01.alicdn.com/kf/Sbfce05ca6f6141208e7002a36a67b224j.jpg',
                'https://ae01.alicdn.com/kf/S2a7d777ff9ac4ebb803ec4fcacd030f0F.jpg',
                'https://ae01.alicdn.com/kf/S53f3577381e14db1a483b3fbd1ffd9efT.jpg',
                'https://ae01.alicdn.com/kf/S4e8d282c16e04892835f9f5fb589f934q.jpg',
            ],
            'stock' => 30,  // Example random integer
        ],
    ];

    public static function createAeProductImport(
        string $id = self::IMPORT_ID,
        int $aeProductId = self::AE_PRODUCT_ID,
        int $completedStep = self::IMPORT_COMPLETED_STEP,
        int $totalSteps = self::IMPORT_TOTAL_STEPS,
    ): AeProductImport {
        $import = new AeProductImport(
            groupData: [],
            id: $id,
            aeProductId: $aeProductId,
            completedStep: $completedStep,
            totalSteps: $totalSteps
        );

        return $import;
    }

    /**
     * @return array<string, mixed>
     */
    public static function createAeDeliveryOption(
        string $code = self::AE_DELIVERY_CODE,
        string $shipsFrom = self::AE_DELIVERY_SHIP_FROM,
        int $minDeliveryDays = self::AE_DELIVERY_MIN_DELIVERY_DAYS,
        int $maxDeliveryDays = self::AE_DELIVERY_MAX_DELIVERY_DAYS,
        int $shippingFeePrice = self::AE_DELIVERY_SHIPPING_FEE,
        string $shippingFeeCurrency = self::AE_DELIVERY_SHIPPING_FEE_CURRENCY,
    ): array {
        return [
            'code' => $code,
            'ship_from_country' => $shipsFrom,
            'max_delivery_days' => $maxDeliveryDays,
            'min_delivery_days' => $minDeliveryDays,
            'shipping_fee_cent' => number_format($shippingFeePrice / 100, 2),
            'shipping_fee_currency' => $shippingFeeCurrency,
        ];
    }
}
