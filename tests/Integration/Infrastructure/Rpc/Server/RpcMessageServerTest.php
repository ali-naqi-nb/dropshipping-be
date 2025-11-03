<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\Server;

use App\Infrastructure\Rpc\RpcMessage;
use App\Infrastructure\Rpc\Server\CommandExecutor\CommandsRegistry;
use App\Infrastructure\Rpc\Server\RpcMessageServer;
use App\Tests\Integration\IntegrationTestCase;

final class RpcMessageServerTest extends IntegrationTestCase
{
    private CommandsRegistry $commandRegistry;
    private RpcMessageServer $messageHandler;
    private mixed $result = null;

    protected function setUp(): void
    {
        /** @var CommandsRegistry $commandRegistry */
        $commandRegistry = self::getContainer()->get(CommandsRegistry::class);
        $this->commandRegistry = $commandRegistry;

        /** @var RpcMessageServer $messageHandler */
        $messageHandler = self::getContainer()->get(RpcMessageServer::class);
        $this->messageHandler = $messageHandler;
    }

    public function testInvoke(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'ping',
            []
        );

        $this->commandRegistry->addController('ping', $this, $rpcMessage->method);
        $this->messageHandler->__invoke($rpcMessage);

        $this->assertNotNull($this->result);
    }

    public function testCommandNotFoundException(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'unknown',
            []
        );

        $this->messageHandler->__invoke($rpcMessage);

        $this->assertNull($this->result);
    }

    public function ping(): void
    {
        $this->result = 'pong';
    }
}
