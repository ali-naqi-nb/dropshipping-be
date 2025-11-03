<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Bus\Event;

use App\Domain\Model\Bus\Event\DomainEventInterface;
use App\Domain\Model\Bus\Event\EventBusInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyEventBus implements EventBusInterface
{
    /**
     * In test env it's in memory, in dev and prod env it's using RabbitMQ.
     *
     * @param MessageBusInterface $eventBus Name is imported because we want to use event bus
     */
    public function __construct(private readonly MessageBusInterface $eventBus)
    {
    }

    public function publish(DomainEventInterface $event): void
    {
        $this->eventBus->dispatch($event);
    }
}
