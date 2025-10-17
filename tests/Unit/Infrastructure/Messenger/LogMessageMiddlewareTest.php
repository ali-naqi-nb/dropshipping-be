<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Messenger;

use App\Domain\Model\Bus\Event\DomainEventInterface;
use App\Infrastructure\Messenger\LogMessageMiddleware;
use App\Tests\Unit\UnitTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

final class LogMessageMiddlewareTest extends UnitTestCase
{
    public function testSendDomainEventMessageIsLogged(): void
    {
        $messageStub = new class() implements DomainEventInterface {
            public function getProp(): string
            {
                return 'test';
            }
        };

        $envelopeMock = $this->createMock(Envelope::class);
        $envelopeMock->expects($this->once())
            ->method('getMessage')
            ->willReturn($messageStub);

        $stackMock = $this->createMock(StackInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with('Sent message: '.$messageStub::class, ['data' => ['prop' => 'test']]);

        $middleware = new LogMessageMiddleware($loggerMock);
        $middleware->handle($envelopeMock, $stackMock);
    }

    public function testSendNonDomainEventMessageIsNotLogged(): void
    {
        $messageStub = new class() {
            public function getProp(): string
            {
                return 'test';
            }
        };

        $envelopeMock = $this->createMock(Envelope::class);
        $envelopeMock->expects($this->once())
            ->method('getMessage')
            ->willReturn($messageStub);

        $stackMock = $this->createMock(StackInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())
            ->method('info');

        $middleware = new LogMessageMiddleware($loggerMock);
        $middleware->handle($envelopeMock, $stackMock);
    }

    public function testReceiveDomainEventMessageIsLogged(): void
    {
        $messageStub = new class() implements DomainEventInterface {
            public function getProp(): string
            {
                return 'test';
            }
        };

        $stampMock = $this->createMock(ReceivedStamp::class);
        $stampMock->expects($this->once())
            ->method('getTransportName')
            ->willReturn('test-transport');

        $envelopeMock = $this->createMock(Envelope::class);
        $envelopeMock->expects($this->once())
            ->method('getMessage')
            ->willReturn($messageStub);
        $envelopeMock->expects($this->once())
            ->method('last')
            ->with(ReceivedStamp::class)
            ->willReturn($stampMock);

        $stackMock = $this->createMock(StackInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $context = ['data' => ['prop' => 'test'], 'transport' => 'test-transport'];
        $loggerMock->expects($this->once())
            ->method('info')
            ->with(
                sprintf('Received message: %s (%s)', $messageStub::class, $context['transport']),
                $context,
            );

        $middleware = new LogMessageMiddleware($loggerMock);
        $middleware->handle($envelopeMock, $stackMock);
    }
}
