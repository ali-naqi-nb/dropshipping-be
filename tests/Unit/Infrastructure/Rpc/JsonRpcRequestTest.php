<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc;

use App\Infrastructure\Rpc\JsonRpcRequest;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Uid\Uuid;

final class JsonRpcRequestTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $jsonrpc = '2.0';
        $method = 'method';
        $params = [
            'param1',
            'param2',
        ];
        $id = Uuid::v4()->__toString();

        $request = new JsonRpcRequest(
            jsonrpc: $jsonrpc,
            method: $method,
            params: $params,
            id: $id,
        );

        $this->assertSame($jsonrpc, $request->getJsonRpc());
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($params, $request->getParams());
        $this->assertSame($id, $request->getId());
    }
}
