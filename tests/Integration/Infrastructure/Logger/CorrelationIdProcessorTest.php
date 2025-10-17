<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Logger;

use App\Infrastructure\Logger\CorrelationIdProcessor;
use App\Infrastructure\Logger\CorrelationIdStorage;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\CorrelationIdFactory;
use Monolog\Level;
use Monolog\LogRecord;

final class CorrelationIdProcessorTest extends IntegrationTestCase
{
    public function testProcessorWithEmptyCorrelationId(): void
    {
        $correlationIdStorage = new CorrelationIdStorage();
        $processor = new CorrelationIdProcessor($correlationIdStorage);
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            Level::Debug,
            'message'
        );

        $this->assertSame(['correlation_id' => '???'], $processor($record)->extra);
    }

    public function testProcessorWithCustomCorrelationId(): void
    {
        $correlationIdStorage = new CorrelationIdStorage();
        $correlationIdStorage->setCorrelationId(CorrelationIdFactory::CORRELATION_ID);
        $processor = new CorrelationIdProcessor($correlationIdStorage);
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'channel',
            Level::Debug,
            'message'
        );

        $this->assertMatchesPattern(['correlation_id' => CorrelationIdFactory::CORRELATION_ID], $processor($record)->extra);
    }
}
