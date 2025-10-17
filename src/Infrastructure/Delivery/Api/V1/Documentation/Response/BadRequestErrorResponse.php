<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'BadRequestErrorResponse',
    description: 'Invalid request data',
    content: new OA\JsonContent(ref: '#/components/schemas/ErrorSchema')
)]
final class BadRequestErrorResponse
{
}
