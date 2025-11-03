<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\Product;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AliExpressProductShippingOptionSchema',
    required: ['code', 'shipsFrom', 'minDeliveryDays', 'maxDeliveryDays', 'shippingFeePrice', 'shippingFeeCurrency'],
    properties: [
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'shipsFrom', type: 'string'),
        new OA\Property(property: 'minDeliveryDays', type: 'string'),
        new OA\Property(property: 'maxDeliveryDays', type: 'string'),
        new OA\Property(property: 'shippingFeePrice', type: 'string'),
        new OA\Property(property: 'shippingFeeCurrency', type: 'string'),
    ],
)]
final class AliExpressProductShippingOptionSchema
{
    public const EXAMPLE_DEFAULT = [
        'code' => 'CAINIAO_FULFILLMENT_STD',
        'shipsFrom' => 'CN',
        'minDeliveryDays' => '20',
        'maxDeliveryDays' => '40',
        'shippingFeePrice' => '1.99',
        'shippingFeeCurrency' => 'USD',
    ];
}
