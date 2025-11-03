<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Messenger;

use App\Infrastructure\Logger\CorrelationIdStorage;
use App\Infrastructure\Messenger\CorrelationIdMiddleware;
use App\Infrastructure\Messenger\CorrelationIdStamp;
use App\Tests\Shared\Factory\CorrelationIdFactory;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class CorrelationIdMiddlewareTest extends UnitTestCase
{
    public function testMiddlewareAddNewCorrelationIdStamp(): void
    {
        $correlationIdStorage = new CorrelationIdStorage();
        $correlationIdStorage->setCorrelationId(CorrelationIdFactory::CORRELATION_ID);

        $envelope = new Envelope(new \stdClass());

        $correlationIdMiddleware = new CorrelationIdMiddleware($correlationIdStorage);

        $stack = $this->createMock(StackInterface::class);

        $nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $stack
            ->method('next')
            ->willReturn($nextMiddleware);

        $nextMiddleware
            ->method('handle')
            ->with(
                $this->callback(function (Envelope $envelope): bool {
                    /** @var CorrelationIdStamp $correlationIdStamp */
                    $correlationIdStamp = $envelope->last(CorrelationIdStamp::class);

                    $this->assertNotNull($correlationIdStamp);
                    $this->assertInstanceOf(CorrelationIdStamp::class, $correlationIdStamp);
                    $this->assertSame(CorrelationIdFactory::CORRELATION_ID, $correlationIdStamp->getId());

                    return true;
                })
            );

        $correlationIdMiddleware->handle($envelope, $stack);
    }
}
