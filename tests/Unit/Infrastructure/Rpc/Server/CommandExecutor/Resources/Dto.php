<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Server\CommandExecutor\Resources;

final class Dto
{
    public function __construct(
        private readonly int $id,
        private readonly string $status,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
