<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

final class RpcResult
{
    public function __construct(
        private readonly int $executedAt,
        private readonly string $commandId,
        private readonly RpcResultStatus $status,
        private readonly mixed $result,
    ) {
    }

    public function getExecutedAt(): int
    {
        return $this->executedAt;
    }

    public function getCommandId(): string
    {
        return $this->commandId;
    }

    public function getStatus(): RpcResultStatus
    {
        return $this->status;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }
}
