<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\Product;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AliExpressProductResponseSchema',
    required: [
        'aeProductId',
        'aeSkuId',
        'aeProductSubject',
        'aeProductCategoryName',
        'aeSkuStock',
        'aeSkuPrice',
        'aeSkuPriceCurrency',
        'aeVariantAttributes',
        'aeProductImageUrls',
        'aeProductShippingOptions',
    ],
    properties: [
        new OA\Property(property: 'aeProductId', type: 'integer'),
        new OA\Property(property: 'aeSkuId', type: 'integer'),
        new OA\Property(property: 'aeProductSubject', type: 'string'),
        new OA\Property(property: 'aeProductCategoryName', type: 'string'),
        new OA\Property(property: 'aeSkuStock', type: 'integer'),
        new OA\Property(property: 'aeSkuPrice', type: 'integer'),
        new OA\Property(property: 'aeSkuPriceCurrency', type: 'integer'),
        new OA\Property(
            property: 'aeVariantAttributes',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AliExpressProductAttributeResponseSchema'),
        ),
        new OA\Property(property: 'aeProductImageUrls', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(
            property: 'aeProductShippingOptions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AliExpressProductShippingOptionSchema'),
        ),
    ],
)]
final class AliExpressProductResponseSchema
{
    public const EXAMPLE_DEFAULT = [
        'aeProductId' => 1005007433426570,
        'aeSkuId' => 12000040736583479,
        'aeProductName' => 'Spring Men\'s Shoes',
        'aeProductCategoryName' => 'Men\'s Shoes',
        'aeSkuStock' => 56,
        'aeSkuPrice' => 12_23,
        'aeSkuPriceCurrency' => 'USD',
        'aeVariantAttributes' => [AliExpressProductAttributeResponseSchema::EXAMPLE_DEFAULT],
        'aeProductImageUrls' => [
            'https://ae01.alicdn.com/kf/Sb65e8c3e8fb64e66ae10d05c93dff1ecn.jpg',
            'https://ae01.alicdn.com/kf/Sb65e8c3e8fb64e66ae10d05c93dff1ecn.jpg',
        ],
        'aeProductShippingOptions' => [AliExpressProductShippingOptionSchema::EXAMPLE_DEFAULT],
    ];
}
