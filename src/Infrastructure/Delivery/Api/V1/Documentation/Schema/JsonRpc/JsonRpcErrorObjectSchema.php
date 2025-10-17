<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\JsonRpc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'JsonRpcErrorObjectSchema',
    required: ['code', 'message'],
    properties: [
        new OA\Property(property: 'code', type: 'integer'),
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', type: 'any'),
    ],
)]
final class JsonRpcErrorObjectSchema
{
    public const EXAMPLE_ERROR = [
        'code' => -32600,
        'message' => 'Invalid request',
    ];
}
