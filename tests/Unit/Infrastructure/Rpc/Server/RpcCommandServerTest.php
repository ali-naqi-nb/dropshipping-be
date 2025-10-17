<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Server;

use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\Server\CommandExecutor\RpcCommandExecutorInterface;
use App\Infrastructure\Rpc\Server\RpcCommandServer;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\RpcResultSenderInterface;
use App\Tests\Shared\Factory\RpcCommandFactory;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Unit\UnitTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class RpcCommandServerTest extends UnitTestCase
{
    private RpcCommandExecutorInterface&MockObject $commandExecutor;
    private RpcResultSenderInterface&MockObject $resultSender;
    private ClockInterface&MockObject $clock;
    private LoggerInterface&MockObject $logger;
    private RpcCommandServer $commandServer;
    private array $logs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandExecutor = $this->createMock(RpcCommandExecutorInterface::class);
        $this->resultSender = $this->createMock(RpcResultSenderInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->commandServer = new RpcCommandServer($this->commandExecutor, $this->resultSender, $this->clock, $this->logger);

        $this->logger->method('debug')
            ->willReturnCallback(function () {
                $this->logs['debug'][] = func_get_args();
            });
    }

    public function testHandle(): void
    {
        $this->clock->method('now')
            ->willReturn(new DateTimeImmutable('@'.(RpcCommandFactory::TIMEOUT_AT - 5)))
        ;

        $rpcCommand = RpcCommandFactory::getRpcCommand();
        $rpcCommandResult = RpcResultFactory::getRpcCommandResult();

        $this->commandExecutor->expects($this->once())
            ->method('execute')
            ->with($rpcCommand->getCommandId(), $rpcCommand->getCommand(), $rpcCommand->getArguments())
            ->willReturn(RpcResultFactory::RESULT);

        $this->resultSender->expects($this->once())
            ->method('send')
            ->with($rpcCommand, $rpcCommandResult);

        $this->commandServer->handle($rpcCommand);

        $expectedLogs = [
            ['RpcServer: command received', [
                'commandId' => RpcCommandFactory::COMMAND_ID,
                'command' => RpcCommandFactory::COMMAND,
                'arguments' => RpcCommandFactory::ARGUMENTS,
                'timeOutAt' => RpcCommandFactory::TIMEOUT_AT,
            ]],
            ['RpcServer: command executed', [
                'executedAt' => RpcResultFactory::EXECUTED_AT,
                'commandId' => RpcResultFactory::COMMAND_ID,
                'status' => RpcResultFactory::STATUS,
                'result' => RpcResultFactory::RESULT,
            ]],
            ['RpcServer: result sent', []],
        ];

        $this->assertSame($expectedLogs, $this->logs['debug']);
    }

    public function testHandleTimeoutBeforeCommandExecution(): void
    {
        $this->clock->method('now')
            ->willReturn(new DateTimeImmutable('@'.(RpcCommandFactory::TIMEOUT_AT + 1)))
        ;

        $rpcCommand = RpcCommandFactory::getRpcCommand();
        $exception = null;

        try {
            $this->commandServer->handle($rpcCommand);
        } catch (TimeoutException $exception) {
        }

        $this->assertInstanceOf(TimeoutException::class, $exception);

        $expectedLogs = [
            ['RpcServer: command received', [
                'commandId' => RpcCommandFactory::COMMAND_ID,
                'command' => RpcCommandFactory::COMMAND,
                'arguments' => RpcCommandFactory::ARGUMENTS,
                'timeOutAt' => RpcCommandFactory::TIMEOUT_AT,
            ]],
            ['RpcServer: the command has timed out before executing the command', []],
        ];

        $this->assertSame($expectedLogs, $this->logs['debug']);
    }

    public function testHandleTimeoutAfterCommandExecution(): void
    {
        $this->clock->method('now')
            ->willReturnOnConsecutiveCalls(
                new DateTimeImmutable('@'.RpcCommandFactory::TIMEOUT_AT),
                new DateTimeImmutable('@'.(RpcCommandFactory::TIMEOUT_AT + 1))
            )
        ;

        $rpcCommand = RpcCommandFactory::getRpcCommand();
        $exception = null;

        $this->commandExecutor->method('execute')
            ->with($rpcCommand->getCommandId(), $rpcCommand->getCommand(), $rpcCommand->getArguments())
            ->willReturn(RpcResultFactory::RESULT);

        $this->resultSender->expects($this->never())
            ->method('send');

        try {
            $this->commandServer->handle($rpcCommand);
        } catch (TimeoutException $exception) {
        }

        $this->assertInstanceOf(TimeoutException::class, $exception);

        $expectedLogs = [
            ['RpcServer: command received', [
                'commandId' => RpcCommandFactory::COMMAND_ID,
                'command' => RpcCommandFactory::COMMAND,
                'arguments' => RpcCommandFactory::ARGUMENTS,
                'timeOutAt' => RpcCommandFactory::TIMEOUT_AT,
            ]],
            ['RpcServer: command executed', [
                'executedAt' => RpcCommandFactory::TIMEOUT_AT + 1,
                'commandId' => RpcResultFactory::COMMAND_ID,
                'status' => RpcResultFactory::STATUS,
                'result' => RpcResultFactory::RESULT,
            ]],
            ['RpcServer: the command has timed out before sending the result', []],
        ];

        $this->assertSame($expectedLogs, $this->logs['debug']);
    }
}
