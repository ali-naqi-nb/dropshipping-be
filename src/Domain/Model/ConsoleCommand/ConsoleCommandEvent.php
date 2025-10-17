<?php

declare(strict_types=1);

namespace App\Domain\Model\ConsoleCommand;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class ConsoleCommandEvent implements DomainEventInterface
{
    public function __construct(
        private readonly string $command,
        private readonly string $service,
        private readonly array $arguments,
    ) {
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function toArray(): array
    {
        return [
            'command' => $this->command,
            'service' => $this->service,
            'arguments' => $this->arguments,
        ];
    }
}
