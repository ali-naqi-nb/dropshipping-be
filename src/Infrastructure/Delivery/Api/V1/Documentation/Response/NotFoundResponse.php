<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'NotFoundResponse',
    description: 'Record or route not found',
    content: new OA\JsonContent(
        examples: [
            'missingData' => new OA\Examples(
                example: 'missingData',
                summary: 'Not Found',
                value: ['message' => 'Not Found']
            ),
        ],
        required: ['message'],
        properties: [new OA\Property(property: 'message', type: 'string')],
    )
)]
final class NotFoundResponse
{
}
