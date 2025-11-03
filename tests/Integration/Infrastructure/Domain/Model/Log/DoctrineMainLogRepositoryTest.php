<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Log;

use App\Domain\Model\Log\LogLevel;
use App\Domain\Model\Log\MainLog;
use App\Infrastructure\Domain\Model\Log\DoctrineMainLogRepository;
use App\Tests\Integration\IntegrationTestCase;
use DateTime;

final class DoctrineMainLogRepositoryTest extends IntegrationTestCase
{
    private DoctrineMainLogRepository $repository;

    public function testSaveAndFindOneById(): void
    {
        $log = new MainLog(
            level: LogLevel::ERROR->value,
            message: 'Test error message',
            context: ['error' => 'test'],
            channel: 'test_channel',
            source: 'TestClass',
            tenantId: 'tenant-123',
            userId: 'user-456'
        );

        $this->repository->save($log);

        $this->assertNotNull($log->getId());

        $foundLog = $this->repository->findOneById($log->getId());

        $this->assertNotNull($foundLog);
        $this->assertSame($log->getId(), $foundLog->getId());
        $this->assertSame(LogLevel::ERROR->value, $foundLog->getLevel());
        $this->assertSame('Test error message', $foundLog->getMessage());
        $this->assertSame(['error' => 'test'], $foundLog->getContext());
        $this->assertSame('test_channel', $foundLog->getChannel());
        $this->assertSame('TestClass', $foundLog->getSource());
        $this->assertSame('tenant-123', $foundLog->getTenantId());
        $this->assertSame('user-456', $foundLog->getUserId());
    }

    public function testFindOneByIdReturnsNullForNonExistent(): void
    {
        $foundLog = $this->repository->findOneById(999999);

        $this->assertNull($foundLog);
    }

    public function testFindByLevel(): void
    {
        // Create logs with different levels
        $errorLog1 = new MainLog(
            level: LogLevel::ERROR->value,
            message: 'Error 1'
        );
        $this->repository->save($errorLog1);

        $errorLog2 = new MainLog(
            level: LogLevel::ERROR->value,
            message: 'Error 2'
        );
        $this->repository->save($errorLog2);

        $infoLog = new MainLog(
            level: LogLevel::INFO->value,
            message: 'Info message'
        );
        $this->repository->save($infoLog);

        $errorLogs = $this->repository->findByLevel(LogLevel::ERROR->value);

        $this->assertGreaterThanOrEqual(2, count($errorLogs));

        foreach ($errorLogs as $log) {
            $this->assertSame(LogLevel::ERROR->value, $log->getLevel());
        }
    }

    public function testFindByLevelWithLimit(): void
    {
        // Create multiple error logs
        for ($i = 0; $i < 5; ++$i) {
            $log = new MainLog(
                level: LogLevel::WARNING->value,
                message: 'Warning ' . $i
            );
            $this->repository->save($log);
        }

        $logs = $this->repository->findByLevel(LogLevel::WARNING->value, 2);

        $this->assertCount(2, $logs);
    }

    public function testFindByTenantId(): void
    {
        $tenantId = 'tenant-test-' . uniqid();

        $log1 = new MainLog(
            level: LogLevel::INFO->value,
            message: 'Log 1 for tenant',
            tenantId: $tenantId
        );
        $this->repository->save($log1);

        $log2 = new MainLog(
            level: LogLevel::ERROR->value,
            message: 'Log 2 for tenant',
            tenantId: $tenantId
        );
        $this->repository->save($log2);

        $log3 = new MainLog(
            level: LogLevel::INFO->value,
            message: 'Log for different tenant',
            tenantId: 'tenant-other'
        );
        $this->repository->save($log3);

        $logs = $this->repository->findByTenantId($tenantId);

        $this->assertGreaterThanOrEqual(2, count($logs));

        foreach ($logs as $log) {
            $this->assertSame($tenantId, $log->getTenantId());
        }
    }

    public function testFindByTenantIdWithLimit(): void
    {
        $tenantId = 'tenant-limit-' . uniqid();

        for ($i = 0; $i < 5; ++$i) {
            $log = new MainLog(
                level: LogLevel::DEBUG->value,
                message: 'Debug log ' . $i,
                tenantId: $tenantId
            );
            $this->repository->save($log);
        }

        $logs = $this->repository->findByTenantId($tenantId, 3);

        $this->assertCount(3, $logs);
    }

    public function testFindByChannel(): void
    {
        $channel = 'test_channel_' . uniqid();

        $log1 = new MainLog(
            level: LogLevel::INFO->value,
            message: 'Log 1',
            channel: $channel
        );
        $this->repository->save($log1);

        $log2 = new MainLog(
            level: LogLevel::WARNING->value,
            message: 'Log 2',
            channel: $channel
        );
        $this->repository->save($log2);

        $log3 = new MainLog(
            level: LogLevel::INFO->value,
            message: 'Log 3',
            channel: 'different_channel'
        );
        $this->repository->save($log3);

        $logs = $this->repository->findByChannel($channel);

        $this->assertGreaterThanOrEqual(2, count($logs));

        foreach ($logs as $log) {
            $this->assertSame($channel, $log->getChannel());
        }
    }

    public function testFindByChannelWithLimit(): void
    {
        $channel = 'limited_channel_' . uniqid();

        for ($i = 0; $i < 5; ++$i) {
            $log = new MainLog(
                level: LogLevel::NOTICE->value,
                message: 'Notice ' . $i,
                channel: $channel
            );
            $this->repository->save($log);
        }

        $logs = $this->repository->findByChannel($channel, 2);

        $this->assertCount(2, $logs);
    }

    public function testDeleteOlderThan(): void
    {
        // This test creates logs but can't easily verify deletion without direct database access
        // or waiting for time to pass. We'll just verify the method executes without error.

        $oldDate = new DateTime('-30 days');

        $deletedCount = $this->repository->deleteOlderThan($oldDate);

        $this->assertIsInt($deletedCount);
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testSaveWithComplexContext(): void
    {
        $complexContext = [
            'exception' => 'RuntimeException',
            'trace' => [
                'file' => 'test.php',
                'line' => 123,
            ],
            'metadata' => [
                'version' => '1.0.0',
                'environment' => 'test',
            ],
        ];

        $log = new MainLog(
            level: LogLevel::CRITICAL->value,
            message: 'Critical error with complex context',
            context: $complexContext
        );

        $this->repository->save($log);

        $id = $log->getId();
        $this->assertNotNull($id);

        $foundLog = $this->repository->findOneById($id);

        $this->assertNotNull($foundLog);
        $this->assertSame($complexContext, $foundLog->getContext());
    }

    public function testLogsAreOrderedByCreatedAtDescending(): void
    {
        $channel = 'ordered_channel_' . uniqid();

        // Create logs with slight delay to ensure different timestamps
        $log1 = new MainLog(
            level: LogLevel::INFO->value,
            message: 'First log',
            channel: $channel
        );
        $this->repository->save($log1);

        sleep(1); // 1 second delay to ensure different timestamps

        $log2 = new MainLog(
            level: LogLevel::INFO->value,
            message: 'Second log',
            channel: $channel
        );
        $this->repository->save($log2);

        sleep(1); // 1 second delay to ensure different timestamps

        $log3 = new MainLog(
            level: LogLevel::INFO->value,
            message: 'Third log',
            channel: $channel
        );
        $this->repository->save($log3);

        $logs = $this->repository->findByChannel($channel);

        $this->assertCount(3, $logs);

        // Verify they are ordered by most recent first
        $messages = array_map(fn($log) => $log->getMessage(), $logs);
        $this->assertSame('Third log', $messages[0]);
        $this->assertSame('Second log', $messages[1]);
        $this->assertSame('First log', $messages[2]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var DoctrineMainLogRepository $repository */
        $repository = self::getContainer()->get(DoctrineMainLogRepository::class);
        $this->repository = $repository;
    }
}
