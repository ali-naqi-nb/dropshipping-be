<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Logger;

use App\Infrastructure\Logger\DataMaskingProcessor;
use App\Tests\Integration\IntegrationTestCase;
use Monolog\Level;
use Monolog\LogRecord;

final class DataMaskingProcessorTest extends IntegrationTestCase
{
    public function testProcessor(): void
    {
        $processor = new DataMaskingProcessor(['password']);
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            Level::Debug,
            'message',
            [
                'password' => 'secret',
                'nested' => ['password' => 'nested password'],
                'passwordPrefix' => 'prefix',
            ]
        );

        $logRecord = $processor($record);

        $this->assertSame(
            [
                'password' => '*****',
                'nested' => ['password' => '*****'],
                'passwordPrefix' => 'prefix',
            ],
            $logRecord->context
        );
    }
}
