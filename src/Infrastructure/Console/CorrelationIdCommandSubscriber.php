<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Infrastructure\Logger\CorrelationIdStorageInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @codeCoverageIgnore
 * TODO: find a way how this can be tested
 */
final class CorrelationIdCommandSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CorrelationIdStorageInterface $correlationIdStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand'],
        ];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        if ('' === $this->correlationIdStorage->getCorrelationId()) {
            $this->correlationIdStorage->setCorrelationId(uniqid('console_'));
        }
    }
}
