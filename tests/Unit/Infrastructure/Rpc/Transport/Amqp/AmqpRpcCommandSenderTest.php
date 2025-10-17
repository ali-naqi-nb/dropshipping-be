<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Transport\Amqp;

use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\Amqp\AmqpRpcCommandSender;
use App\Tests\Shared\Factory\RpcCommandFactory;
use App\Tests\Unit\UnitTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

final class AmqpRpcCommandSenderTest extends UnitTestCase
{
    private MessageBusInterface&MockObject $bus;
    private ClockInterface&MockObject $clock;
    private LoggerInterface&MockObject $logger;
    private AmqpRpcCommandSender $commandSender;

    protected function setUp(): void
    {
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->commandSender = new AmqpRpcCommandSender($this->bus, $this->clock, $this->logger);
    }

    public function testSend(): void
    {
        $this->clock->method('now')
            ->willReturn(new DateTimeImmutable('@'.RpcCommandFactory::SENT_AT))
        ;

        $rpcCommand = RpcCommandFactory::getRpcCommand();

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with($rpcCommand, [
                new AmqpStamp(routingKey: $rpcCommand->getCommand(), flags: 0, attributes: [
                    'expiration' => (RpcCommandFactory::TIMEOUT_AT - RpcCommandFactory::SENT_AT) * 1_000,
                ]),
            ]);

        $this->commandSender->send($rpcCommand);
    }

    public function testTimeoutBeforeSending(): void
    {
        $this->clock->method('now')
            ->willReturn(new DateTimeImmutable('@'.RpcCommandFactory::TIMEOUT_AT))
        ;

        $rpcCommand = RpcCommandFactory::getRpcCommand();

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('AmqpRpcCommandSender: the command has timed out before sending the command');

        $this->bus->expects($this->never())
            ->method('dispatch');

        try {
            $this->commandSender->send($rpcCommand);
            $this->fail('TimeoutException should be thrown');
        } catch (TimeoutException) {
        }
    }
}
