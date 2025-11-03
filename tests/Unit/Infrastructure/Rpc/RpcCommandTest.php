<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc;

use App\Infrastructure\Rpc\RpcCommand;
use App\Tests\Shared\Factory\RpcCommandFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class RpcCommandTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $rpcResult = new RpcCommand(
            RpcCommandFactory::SENT_AT,
            RpcCommandFactory::TIMEOUT_AT,
            RpcCommandFactory::COMMAND_ID,
            RpcCommandFactory::COMMAND,
            RpcCommandFactory::ARGUMENTS,
            TenantFactory::TENANT_ID,
        );

        $this->assertSame(RpcCommandFactory::SENT_AT, $rpcResult->getSentAt());
        $this->assertSame(RpcCommandFactory::TIMEOUT_AT, $rpcResult->getTimeoutAt());
        $this->assertSame(RpcCommandFactory::COMMAND_ID, $rpcResult->getCommandId());
        $this->assertSame(RpcCommandFactory::COMMAND, $rpcResult->getCommand());
        $this->assertSame(RpcCommandFactory::ARGUMENTS, $rpcResult->getArguments());
        $this->assertSame(TenantFactory::TENANT_ID, $rpcResult->getTenantId());
    }
}
