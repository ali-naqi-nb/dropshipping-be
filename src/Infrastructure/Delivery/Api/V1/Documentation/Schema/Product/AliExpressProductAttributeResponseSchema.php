<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\Product;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AliExpressProductAttributeResponseSchema',
    required: ['aeVariantAttributeName', 'aeVariantAttributeType', 'aeVariantAttributeValue'],
    properties: [
        new OA\Property(property: 'aeVariantAttributeName', type: 'string'),
        new OA\Property(property: 'aeVariantAttributeType', ref: '#/components/schemas/AeAttributeTypeSchema'),
        new OA\Property(property: 'aeVariantAttributeValue', type: 'string'),
    ],
)]
final class AliExpressProductAttributeResponseSchema
{
    public const EXAMPLE_DEFAULT = [
        'aeVariantAttributeName' => 'Color',
        'aeVariantAttributeType' => AeAttributeTypeSchema::SKU_PROPERTY,
        'aeVariantAttributeValue' => 'black',
    ];
}
