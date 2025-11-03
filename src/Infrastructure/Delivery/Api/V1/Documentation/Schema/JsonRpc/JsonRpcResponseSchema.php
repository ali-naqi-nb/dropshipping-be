<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\JsonRpc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'JsonRpcResponseSchema',
    required: ['jsonrpc', 'id'],
    properties: [
        new OA\Property(property: 'jsonrpc', type: 'string', default: '2.0'),
        new OA\Property(property: 'result', type: 'AnyValue'),
        new OA\Property(property: 'error', ref: '#/components/schemas/JsonRpcErrorObjectSchema'),
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    ]
)]
final class JsonRpcResponseSchema
{
    public const EXAMPLE_ERROR = [
        'jsonrpc' => '2.0',
        'error' => JsonRpcErrorObjectSchema::EXAMPLE_ERROR,
        'id' => '1ecbbfe3-3612-661a-9573-d167916b69b9',
    ];

    // TODO: add rpc command response examples
}
