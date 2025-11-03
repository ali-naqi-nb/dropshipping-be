<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'UnauthorizedResponse',
    description: 'Unauthorized.',
    content: new OA\JsonContent(
        examples: [
            'unauthorized' => new OA\Examples(
                example: 'unauthorized',
                summary: 'Unauthorized',
                value: [
                    'message' => 'Unauthorized',
                ],
            ),
        ],
        required: ['message'],
        properties: [
            new OA\Property(property: 'message', type: 'string'),
        ]
    )
)]
final class UnauthorizedErrorResponse
{
}
