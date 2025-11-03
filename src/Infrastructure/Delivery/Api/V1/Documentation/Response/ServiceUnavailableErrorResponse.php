<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'ServiceUnavailableErrorResponse',
    description: 'Service is unavailable',
    content: new OA\JsonContent(
        examples: [
            'default' => new OA\Examples(
                example: 'default',
                summary: 'Service is unavailable',
                value: ['message' => 'Service is unavailable.']
            ),
        ],
        required: ['message'],
        properties: [new OA\Property(property: 'message', type: 'string')],
    ),
)]
final class ServiceUnavailableErrorResponse
{
}
