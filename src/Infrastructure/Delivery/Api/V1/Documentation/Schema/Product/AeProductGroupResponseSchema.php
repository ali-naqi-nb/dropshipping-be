<?php

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\Product;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AeProductGroupResponseSchema',
    required: [
        'id', 'aeProductId', 'progressStep', 'totalSteps',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'aeProductId', type: 'integer'),
        new OA\Property(property: 'progressStep', type: 'integer'),
        new OA\Property(property: 'totalSteps', type: 'integer'),
    ]
)]
final class AeProductGroupResponseSchema
{
    public const EXAMPLE_DEFAULT = [
        'id' => "5c1b2dac-f45c-473c-8ee6-887145c7ef60",
        'aeProductId' => 1005007433426570,
        'progressStep' => 3,
        'totalSteps' => 5,
    ];
}
