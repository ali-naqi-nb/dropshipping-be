<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response\App;

use App\Infrastructure\Delivery\Api\V1\Documentation\Schema\App\AppResponseSchema;
use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'SingleAppResponse',
    description: 'Successful app response',
    content: new OA\JsonContent(
        examples: [
            'default' => new OA\Examples(
                example: 'default',
                summary: 'Default',
                value: ['data' => AppResponseSchema::EXAMPLE_ALI_EXPRESS],
            ),
        ],
        required: ['data'],
        properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AppResponseSchema')]
    ),
)]
final class SingleAppResponse
{
}
