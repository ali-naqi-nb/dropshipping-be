<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use Monolog\LogRecord;

final class DataMaskingProcessor
{
    private const MASK = '*****';

    public function __construct(private readonly array $sensitiveData = [])
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        array_walk_recursive(
            $context,
            function (&$value, int|string $key) {
                if (in_array($key, $this->sensitiveData, true)) {
                    $value = self::MASK;
                }
            }
        );

        return new LogRecord(
            $record->datetime,
            $record->channel,
            $record->level,
            $record->message,
            $context,
            $record->extra,
            $record->formatted
        );
    }
}
