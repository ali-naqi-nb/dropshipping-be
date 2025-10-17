<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use Monolog\LogRecord;

final class CorrelationIdProcessor
{
    public function __construct(private readonly CorrelationIdStorageInterface $correlationIdStorage)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['correlation_id'] = $this->correlationIdStorage->getCorrelationId() ?: '???';

        return $record;
    }
}
