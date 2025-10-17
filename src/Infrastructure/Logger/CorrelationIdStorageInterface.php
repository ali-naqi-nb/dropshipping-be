<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

interface CorrelationIdStorageInterface
{
    public function getCorrelationId(): string;

    public function setCorrelationId(string $correlationId): void;
}
