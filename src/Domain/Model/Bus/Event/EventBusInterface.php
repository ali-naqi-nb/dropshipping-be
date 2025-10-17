<?php

declare(strict_types=1);

namespace App\Domain\Model\Bus\Event;

interface EventBusInterface
{
    public function publish(DomainEventInterface $event): void;
}
