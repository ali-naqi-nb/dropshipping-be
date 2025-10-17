<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Client;

use App\Infrastructure\Rpc\Client\RpcMessageClient;
use App\Infrastructure\Rpc\RpcMessage;
use App\Infrastructure\Rpc\Service\CallIdGeneratorInterface;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class RpcMessageClientTest extends UnitTestCase
{
    private CallIdGeneratorInterface&MockObject $messageIdGenerator;
    private MessageBusInterface&MockObject $bus;
    private LoggerInterface&MockObject $logger;
    private RpcMessageClient $messageClient;
    private array $logs = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->messageIdGenerator = $this->createMock(CallIdGeneratorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageClient = new RpcMessageClient($this->messageIdGenerator, $this->bus, $this->logger);

        $this->logger->method('debug')
            ->willReturnCallback(function () {
                $this->logs['debug'][] = func_get_args();
            });
    }

    public function testRequest(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            ['arg1', 'arg2']
        );

        $this->messageIdGenerator->expects($this->once())
            ->method('generate')
            ->willReturn($rpcMessage->id);

        $this->bus->expects($this->once())
            ->method('dispatch');

        $this->messageClient->request($rpcMessage->method, $rpcMessage->arguments, $rpcMessage->onError, $rpcMessage->onSuccess);

        $expectedLogs = [
            ['RpcMessage request sent', $rpcMessage->toArray()],
        ];

        $this->assertSame($expectedLogs, $this->logs['debug']);
    }

    public function testReply(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            ['arg1', 'arg2'],
            onSuccess: 'success'
        );

        $this->messageIdGenerator->expects($this->never())
            ->method('generate');

        $this->bus->expects($this->once())
            ->method('dispatch');

        $this->messageClient->reply($rpcMessage->id, 'success', [RpcResultFactory::RESULT]);

        $expectedLogs = [
            ['RpcMessage reply sent', [
                'id' => $rpcMessage->id,
                'method' => $rpcMessage->onSuccess,
                'arguments' => [RpcResultFactory::RESULT],
                'onError' => null,
                'onSuccess' => null,
            ]],
        ];

        $this->assertSame($expectedLogs, $this->logs['debug']);
    }
}
