<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

final class RpcCommand
{
    public function __construct(
        private readonly int $sentAt,
        private readonly int $timeoutAt,
        private readonly string $commandId,
        private readonly string $command,
        private readonly array $arguments,
        private readonly ?string $tenantId = null,
    ) {
    }

    public function getSentAt(): int
    {
        return $this->sentAt;
    }

    public function getTimeoutAt(): int
    {
        return $this->timeoutAt;
    }

    public function getCommandId(): string
    {
        return $this->commandId;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
}
