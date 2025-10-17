<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Server;

use App\Infrastructure\Rpc\Client\RpcMessageClientInterface;
use App\Infrastructure\Rpc\Exception\CommandNotFoundException;
use App\Infrastructure\Rpc\RpcMessage;
use App\Infrastructure\Rpc\Server\CommandExecutor\RpcCommandExecutorInterface;
use App\Infrastructure\Rpc\Server\RpcMessageServer;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class RpcMessageServerTest extends UnitTestCase
{
    private RpcCommandExecutorInterface&MockObject $commandExecutor;
    private RpcMessageClientInterface&MockObject $publisher;
    private LoggerInterface&MockObject $logger;
    private RpcMessageServer $messageServer;
    private array $logs = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandExecutor = $this->createMock(RpcCommandExecutorInterface::class);
        $this->publisher = $this->createMock(RpcMessageClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageServer = new RpcMessageServer($this->publisher, $this->commandExecutor, $this->logger);

        $this->logger->method('debug')
            ->willReturnCallback(function () {
                $this->logs['debug'][] = func_get_args();
            });
        $this->logger->method('error')
            ->willReturnCallback(function () {
                $this->logs['error'][] = func_get_args();
            });
    }

    public function testInvoke(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            ['arg1', 'arg2']
        );

        $this->commandExecutor->expects($this->once())
            ->method('execute')
            ->with($rpcMessage->id, $rpcMessage->method, $rpcMessage->arguments)
            ->willReturn(RpcResultFactory::RESULT);

        $this->publisher->expects($this->never())
            ->method('reply');

        $this->messageServer->__invoke($rpcMessage);

        $expectedLogs = [
            ['RpcMessage received', $rpcMessage->toArray()],
        ];

        $this->assertSame($expectedLogs, $this->logs['debug']);
    }

    public function testReplySuccess(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            ['arg1', 'arg2'],
            onSuccess: 'success'
        );

        $this->commandExecutor->expects($this->once())
            ->method('execute')
            ->with($rpcMessage->id, $rpcMessage->method, $rpcMessage->arguments)
            ->willReturn(RpcResultFactory::RESULT);

        $this->publisher->expects($this->once())
            ->method('reply')
            ->with($rpcMessage->id, $rpcMessage->onSuccess, [RpcResultFactory::RESULT]);

        $this->messageServer->__invoke($rpcMessage);

        $expectedLogs = [
            ['RpcMessage received', $rpcMessage->toArray()],
        ];

        $this->assertSame($expectedLogs, $this->logs['debug']);
    }

    public function testReplyError(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            ['arg1', 'arg2'],
            onError: 'error'
        );

        $this->commandExecutor->expects($this->once())
            ->method('execute')
            ->with($rpcMessage->id, $rpcMessage->method, $rpcMessage->arguments)
            ->willReturn(RpcResultFactory::RESULT);

        $this->publisher->expects($this->never())
            ->method('reply');

        $this->messageServer->__invoke($rpcMessage);

        $expectedLogs = [
            ['RpcMessage received', $rpcMessage->toArray()],
        ];

        $this->assertSame($expectedLogs, $this->logs['debug']);
    }

    public function testCommandNotFoundException(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            ['arg1', 'arg2'],
        );

        $this->commandExecutor->expects($this->once())
            ->method('execute')
            ->with($rpcMessage->id, $rpcMessage->method, $rpcMessage->arguments)
            ->willThrowException(new CommandNotFoundException());

        $this->publisher->expects($this->never())
            ->method('reply');

        $this->messageServer->__invoke($rpcMessage);

        $expectedLogs = [
            ['Command not found', []],
        ];

        $this->assertSame($expectedLogs, $this->logs['error']);
    }

    public function testExecutorException(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            ['arg1', 'arg2'],
        );

        $this->commandExecutor->expects($this->once())
            ->method('execute')
            ->with($rpcMessage->id, $rpcMessage->method, $rpcMessage->arguments)
            ->willThrowException(new \Exception('Oops!'));

        $this->publisher->expects($this->never())
            ->method('reply');

        $this->messageServer->__invoke($rpcMessage);

        $expectedLogs = [
            ['RpcMessage handler error: Oops!', []],
        ];

        $this->assertSame($expectedLogs, $this->logs['error']);
    }
}
