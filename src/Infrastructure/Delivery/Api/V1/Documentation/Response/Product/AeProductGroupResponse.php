<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response\Product;

use App\Infrastructure\Delivery\Api\V1\Documentation\Schema\Product\AeProductGroupResponseSchema;
use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'AeProductGroupResponse',
    description: 'Successful product list response',
    content: new OA\JsonContent(
        examples: [
            'default' => new OA\Examples(
                example: 'default',
                summary: 'Default',
                value: [
                    'data' => [
                        'items' => [AeProductGroupResponseSchema::EXAMPLE_DEFAULT],
                    ],
                ]
            ),
        ],
        required: ['data'],
        properties: [
            new OA\Property(
                property: 'data',
                required: ['items'],
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/AeProductGroupResponseSchema'),
                    ),
                ],
            ),
        ],
    ),
)]
final class AeProductGroupResponse
{
}
