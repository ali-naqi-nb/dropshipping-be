<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

final class CorrelationIdStorage implements CorrelationIdStorageInterface
{
    private string $correlationId = '';

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function setCorrelationId(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }
}
