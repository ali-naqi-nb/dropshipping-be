<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc;

use App\Infrastructure\Rpc\RpcResult;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Unit\UnitTestCase;

final class RpcResultTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $rpcResult = new RpcResult(
            RpcResultFactory::EXECUTED_AT,
            RpcResultFactory::COMMAND_ID,
            RpcResultFactory::STATUS,
            RpcResultFactory::RESULT,
        );

        $this->assertSame(RpcResultFactory::EXECUTED_AT, $rpcResult->getExecutedAt());
        $this->assertSame(RpcResultFactory::COMMAND_ID, $rpcResult->getCommandId());
        $this->assertSame(RpcResultFactory::STATUS, $rpcResult->getStatus());
        $this->assertSame(RpcResultFactory::RESULT, $rpcResult->getResult());
    }
}
