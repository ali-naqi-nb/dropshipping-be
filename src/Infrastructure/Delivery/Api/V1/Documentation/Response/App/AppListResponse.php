<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response\App;

use App\Infrastructure\Delivery\Api\V1\Documentation\Schema\App\AppResponseSchema;
use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'AppListResponse',
    description: 'Successful app list response',
    content: new OA\JsonContent(
        examples: [
            'default' => new OA\Examples(
                example: 'default',
                summary: 'Default',
                value: [
                    'data' => [
                        'items' => [
                            AppResponseSchema::EXAMPLE_ALI_EXPRESS,
                        ],
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
                        items: new OA\Items(ref: '#/components/schemas/AppResponseSchema'),
                    ),
                ],
            ),
        ],
    ),
)]
final class AppListResponse
{
}
