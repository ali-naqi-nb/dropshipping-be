<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\Product;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AeAttributeTypeSchema',
    type: 'string',
    enum: ['sku_property', 'attribute'],
)]
final class AeAttributeTypeSchema
{
    public const SKU_PROPERTY = 'sku_property';
    public const ATTRIBUTE = 'attribute';
}
