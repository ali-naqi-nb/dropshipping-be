<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Log;

use App\Domain\Model\Log\LogLevel;
use App\Domain\Model\Log\MainLog;
use App\Tests\Unit\UnitTestCase;
use DateTime;

final class MainLogTest extends UnitTestCase
{
    public function testConstructorAndGetters(): void
    {
        $level = LogLevel::ERROR->value;
        $message = 'Test error message';
        $context = ['key' => 'value', 'productId' => 12345];
        $channel = 'test_channel';
        $source = 'TestClass';
        $tenantId = 'tenant-123';
        $userId = 'user-456';

        $log = new MainLog(
            level: $level,
            message: $message,
            context: $context,
            channel: $channel,
            source: $source,
            tenantId: $tenantId,
            userId: $userId
        );

        $this->assertNull($log->getId());
        $this->assertSame($level, $log->getLevel());
        $this->assertSame($message, $log->getMessage());
        $this->assertSame($context, $log->getContext());
        $this->assertSame($channel, $log->getChannel());
        $this->assertSame($source, $log->getSource());
        $this->assertSame($tenantId, $log->getTenantId());
        $this->assertSame($userId, $log->getUserId());
        $this->assertInstanceOf(DateTime::class, $log->getCreatedAt());
    }

    public function testConstructorWithMinimalParameters(): void
    {
        $level = LogLevel::INFO->value;
        $message = 'Simple log message';

        $log = new MainLog(
            level: $level,
            message: $message
        );

        $this->assertNull($log->getId());
        $this->assertSame($level, $log->getLevel());
        $this->assertSame($message, $log->getMessage());
        $this->assertNull($log->getContext());
        $this->assertNull($log->getChannel());
        $this->assertNull($log->getSource());
        $this->assertNull($log->getTenantId());
        $this->assertNull($log->getUserId());
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
            $log = new MainLog(
                level: $logLevel->value,
                message: 'Test message for ' . $logLevel->value
            );

            $this->assertSame($logLevel->value, $log->getLevel());
        }
    }

    public function testSetContext(): void
    {
        $log = new MainLog(
            level: LogLevel::ERROR->value,
            message: 'Test message'
        );

        $this->assertNull($log->getContext());

        $newContext = ['error' => 'Connection timeout', 'attempts' => 3];
        $log->setContext($newContext);

        $this->assertSame($newContext, $log->getContext());
    }

    public function testSetChannel(): void
    {
        $log = new MainLog(
            level: LogLevel::INFO->value,
            message: 'Test message'
        );

        $this->assertNull($log->getChannel());

        $newChannel = 'payment';
        $log->setChannel($newChannel);

        $this->assertSame($newChannel, $log->getChannel());
    }

    public function testSetSource(): void
    {
        $log = new MainLog(
            level: LogLevel::WARNING->value,
            message: 'Test message'
        );

        $this->assertNull($log->getSource());

        $newSource = 'PaymentProcessor';
        $log->setSource($newSource);

        $this->assertSame($newSource, $log->getSource());
    }

    public function testSetTenantId(): void
    {
        $log = new MainLog(
            level: LogLevel::ERROR->value,
            message: 'Test message'
        );

        $this->assertNull($log->getTenantId());

        $newTenantId = 'tenant-789';
        $log->setTenantId($newTenantId);

        $this->assertSame($newTenantId, $log->getTenantId());
    }

    public function testSetUserId(): void
    {
        $log = new MainLog(
            level: LogLevel::DEBUG->value,
            message: 'Test message'
        );

        $this->assertNull($log->getUserId());

        $newUserId = 'user-999';
        $log->setUserId($newUserId);

        $this->assertSame($newUserId, $log->getUserId());
    }

    public function testCreatedAtIsAutomaticallySet(): void
    {
        $beforeCreation = new DateTime();

        $log = new MainLog(
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
            'exception' => 'RuntimeException',
            'trace' => ['file' => 'test.php', 'line' => 123],
            'metadata' => [
                'environment' => 'test',
                'version' => '1.0.0',
            ],
            'count' => 42,
        ];

        $log = new MainLog(
            level: LogLevel::CRITICAL->value,
            message: 'Critical error occurred',
            context: $complexContext
        );

        $this->assertSame($complexContext, $log->getContext());
    }
}
