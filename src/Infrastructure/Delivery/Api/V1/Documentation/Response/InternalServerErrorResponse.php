<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'InternalServerErrorResponse',
    description: 'Internal server error',
    content: new OA\JsonContent(
        examples: [
            'default' => new OA\Examples(
                example: 'default',
                summary: 'Internal server error',
                value: ['message' => 'Internal server error']
            ),
        ],
        required: ['message'],
        properties: [new OA\Property(property: 'message', type: 'string')],
    ),
)]
final class InternalServerErrorResponse
{
}
