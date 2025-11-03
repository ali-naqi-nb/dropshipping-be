<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

final class LoggerFactory
{
    public const LOG_MESSAGE = 'test';

    public const LOG_FORMAT = '%extra.correlation_id% %message%';

    public const CORRELATION_ID = 'e1c63c4e-701b-4ca7-a63c-7818f79038dc';

    public static function getRecord(string $message = self::LOG_MESSAGE, array $context = []): array
    {
        return [
            'message' => $message,
            'context' => array_replace($context, []),
            'extra' => ['correlation_id' => self::CORRELATION_ID],
        ];
    }
}
