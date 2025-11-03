<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Log;

use App\Domain\Model\Log\LogLevel;
use App\Domain\Model\Log\TenantLog;
use App\Tests\Unit\UnitTestCase;
use DateTime;

final class TenantLogTest extends UnitTestCase
{
    public function testConstructorAndGetters(): void
    {
        $level = LogLevel::ERROR->value;
        $message = 'Test error message';
        $context = ['key' => 'value', 'orderId' => 'ORD-123'];
        $channel = 'order_processing';
        $source = 'OrderService';
        $userId = 'user-789';
        $requestId = 'req-abc123';
        $stackTrace = 'Stack trace line 1\nStack trace line 2';

        $log = new TenantLog(
            level: $level,
            message: $message,
            context: $context,
            channel: $channel,
            source: $source,
            userId: $userId,
            requestId: $requestId,
            stackTrace: $stackTrace
        );

        $this->assertNull($log->getId());
        $this->assertSame($level, $log->getLevel());
        $this->assertSame($message, $log->getMessage());
        $this->assertSame($context, $log->getContext());
        $this->assertSame($channel, $log->getChannel());
        $this->assertSame($source, $log->getSource());
        $this->assertSame($userId, $log->getUserId());
        $this->assertSame($requestId, $log->getRequestId());
        $this->assertSame($stackTrace, $log->getStackTrace());
        $this->assertInstanceOf(DateTime::class, $log->getCreatedAt());
    }

    public function testConstructorWithMinimalParameters(): void
    {
        $level = LogLevel::INFO->value;
        $message = 'Simple tenant log message';

        $log = new TenantLog(
            level: $level,
            message: $message
        );

        $this->assertNull($log->getId());
        $this->assertSame($level, $log->getLevel());
        $this->assertSame($message, $log->getMessage());
        $this->assertNull($log->getContext());
        $this->assertNull($log->getChannel());
        $this->assertNull($log->getSource());
        $this->assertNull($log->getUserId());
        $this->assertNull($log->getRequestId());
        $this->assertNull($log->getStackTrace());
        $this->assertInstanceOf(DateTime::class, $log->getCreatedAt());
    }

    public function testAllLogLevels(): void
    {
        $logLevels = [
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ];

        foreach ($logLevels as $logLevel) {
            $log = new TenantLog(
                level: $logLevel->value,
                message: 'Test message for ' . $logLevel->value
            );

            $this->assertSame($logLevel->value, $log->getLevel());
        }
    }

    public function testSetContext(): void
    {
        $log = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'Test message'
        );

        $this->assertNull($log->getContext());

        $newContext = ['error' => 'API timeout', 'endpoint' => '/api/products'];
        $log->setContext($newContext);

        $this->assertSame($newContext, $log->getContext());
    }

    public function testSetChannel(): void
    {
        $log = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'Test message'
        );

        $this->assertNull($log->getChannel());

        $newChannel = 'api';
        $log->setChannel($newChannel);

        $this->assertSame($newChannel, $log->getChannel());
    }

    public function testSetSource(): void
    {
        $log = new TenantLog(
            level: LogLevel::WARNING->value,
            message: 'Test message'
        );

        $this->assertNull($log->getSource());

        $newSource = 'ApiController';
        $log->setSource($newSource);

        $this->assertSame($newSource, $log->getSource());
    }

    public function testSetUserId(): void
    {
        $log = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'Test message'
        );

        $this->assertNull($log->getUserId());

        $newUserId = 'user-555';
        $log->setUserId($newUserId);

        $this->assertSame($newUserId, $log->getUserId());
    }

    public function testSetRequestId(): void
    {
        $log = new TenantLog(
            level: LogLevel::DEBUG->value,
            message: 'Test message'
        );

        $this->assertNull($log->getRequestId());

        $newRequestId = 'req-xyz789';
        $log->setRequestId($newRequestId);

        $this->assertSame($newRequestId, $log->getRequestId());
    }

    public function testSetStackTrace(): void
    {
        $log = new TenantLog(
            level: LogLevel::CRITICAL->value,
            message: 'Test message'
        );

        $this->assertNull($log->getStackTrace());

        $newStackTrace = '#0 test.php(10): method()\n#1 main.php(5): call()';
        $log->setStackTrace($newStackTrace);

        $this->assertSame($newStackTrace, $log->getStackTrace());
    }

    public function testCreatedAtIsAutomaticallySet(): void
    {
        $beforeCreation = new DateTime();

        $log = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'Test message'
        );

        $afterCreation = new DateTime();

        $this->assertGreaterThanOrEqual($beforeCreation, $log->getCreatedAt());
        $this->assertLessThanOrEqual($afterCreation, $log->getCreatedAt());
    }

    public function testComplexContext(): void
    {
        $complexContext = [
            'request' => [
                'method' => 'POST',
                'uri' => '/api/orders',
                'body' => ['items' => [1, 2, 3]],
            ],
            'response' => [
                'status' => 500,
                'error' => 'Internal Server Error',
            ],
            'timing' => [
                'duration_ms' => 1234,
                'timestamp' => '2024-10-21T12:00:00Z',
            ],
        ];

        $log = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'API request failed',
            context: $complexContext
        );

        $this->assertSame($complexContext, $log->getContext());
    }

    public function testWithRequestIdForTracing(): void
    {
        $requestId = 'req-trace-' . bin2hex(random_bytes(8));

        $log = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'Processing request',
            requestId: $requestId
        );

        $this->assertSame($requestId, $log->getRequestId());
    }
}
