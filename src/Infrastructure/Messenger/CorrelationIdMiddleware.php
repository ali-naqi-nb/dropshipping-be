<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Infrastructure\Logger\CorrelationIdStorageInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class CorrelationIdMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly CorrelationIdStorageInterface $correlationIdStorage)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last(CorrelationIdStamp::class)) {
            $correlationId = $this->correlationIdStorage->getCorrelationId();
            $envelope = $envelope->with(new CorrelationIdStamp($correlationId));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
