<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Response\JsonRpc;

use App\Infrastructure\Delivery\Api\V1\Documentation\Schema\JsonRpc\JsonRpcResponseSchema;
use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'JsonRpcResponse',
    description: 'JSON RPC response',
    content: new OA\JsonContent(
        examples: [
            'error' => new OA\Examples(
                example: 'error',
                summary: 'Error',
                value: JsonRpcResponseSchema::EXAMPLE_ERROR,
            ),
            // TODO: add rpc command response
        ],
        ref: '#/components/schemas/JsonRpcResponseSchema',
    ),
)]
final class JsonRpcResponse
{
}
