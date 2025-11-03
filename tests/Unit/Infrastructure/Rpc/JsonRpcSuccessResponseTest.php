<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc;

use App\Infrastructure\Rpc\JsonRpcSuccessResponse;
use App\Infrastructure\Rpc\RpcResult;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Uid\Uuid;

final class JsonRpcSuccessResponseTest extends UnitTestCase
{
    public function testFromRpcResult(): void
    {
        $rpcResult = new RpcResult(
            RpcResultFactory::EXECUTED_AT,
            RpcResultFactory::COMMAND_ID,
            RpcResultFactory::STATUS,
            RpcResultFactory::RESULT,
        );

        $id = Uuid::v4()->__toString();

        $response = JsonRpcSuccessResponse::fromRpcResult($id, $rpcResult);

        $this->assertSame('2.0', $response->getJsonrpc());
        $this->assertSame($rpcResult->getResult(), $response->getResult());
        $this->assertSame($id, $response->getId());
    }
}
