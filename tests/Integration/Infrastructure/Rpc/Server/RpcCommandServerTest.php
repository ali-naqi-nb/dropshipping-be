<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\Server;

use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Infrastructure\Rpc\Server\CommandExecutor\CommandsRegistry;
use App\Infrastructure\Rpc\Server\RpcCommandServerInterface;
use App\Infrastructure\Rpc\Transport\RpcResultSenderInterface;
use App\Tests\Double\Rpc\Transport\MockResultSender;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\RpcCommandFactory;

final class RpcCommandServerTest extends IntegrationTestCase
{
    private RpcCommandServerInterface $server;
    private MockResultSender $resultSender;
    private CommandsRegistry $commandsRegistry;

    protected function setUp(): void
    {
        /** @var RpcCommandServerInterface $server */
        $server = self::getContainer()->get(RpcCommandServerInterface::class);
        $this->server = $server;

        /** @var CommandsRegistry $commandsRegistry */
        $commandsRegistry = self::getContainer()->get(CommandsRegistry::class);
        $this->commandsRegistry = $commandsRegistry;

        /** @var MockResultSender $resultSender */
        $resultSender = self::getContainer()->get(RpcResultSenderInterface::class);
        $this->resultSender = $resultSender;
    }

    public function testHandle(): void
    {
        $rpcCommand = RpcCommandFactory::getRpcCommand(
            sentAt: time(),
            timeoutAt: time() + 60,
            command: 'ping',
        );

        $this->commandsRegistry->addController('ping', $this, 'commandHandler');

        $this->server->handle($rpcCommand);

        /** @var RpcResult $result */
        $result = $this->resultSender->getSent($rpcCommand);

        $this->assertSame(RpcResultStatus::SUCCESS, $result->getStatus());
        $this->assertSame('pong', $result->getResult());
    }

    public function testHandleNonExistingCommand(): void
    {
        $rpcCommand = RpcCommandFactory::getRpcCommand(
            sentAt: time(),
            timeoutAt: time() + 60,
            command: 'non_existing_command',
        );

        $this->server->handle($rpcCommand);

        /** @var RpcResult $result */
        $result = $this->resultSender->getSent($rpcCommand);

        $this->assertSame(RpcResultStatus::ERROR, $result->getStatus());
        $this->assertSame([
            'code' => -32601,
            'message' => 'Command "non_existing_command" not found',
        ], $result->getResult());
    }

    public function commandHandler(): string
    {
        return 'pong';
    }
}
