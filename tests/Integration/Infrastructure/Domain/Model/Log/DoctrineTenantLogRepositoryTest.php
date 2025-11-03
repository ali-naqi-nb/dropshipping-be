<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Log;

use App\Domain\Model\Log\LogLevel;
use App\Domain\Model\Log\TenantLog;
use App\Infrastructure\Domain\Model\Log\DoctrineTenantLogRepository;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use DateTime;
use Doctrine\DBAL\Exception;

final class DoctrineTenantLogRepositoryTest extends IntegrationTestCase
{
    private DoctrineTenantLogRepository $repository;
    private DoctrineTenantConnection $connection;

    public function testSaveAndFindOneById(): void
    {
        $log = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'Test error message',
            context: ['error' => 'API timeout'],
            channel: 'api',
            source: 'ApiController',
            userId: 'user-789',
            requestId: 'req-abc123',
            stackTrace: '#0 test.php(10): method()'
        );

        $this->repository->save($log);

        $this->assertNotNull($log->getId());

        $foundLog = $this->repository->findOneById($log->getId());

        $this->assertNotNull($foundLog);
        $this->assertSame($log->getId(), $foundLog->getId());
        $this->assertSame(LogLevel::ERROR->value, $foundLog->getLevel());
        $this->assertSame('Test error message', $foundLog->getMessage());
        $this->assertSame(['error' => 'API timeout'], $foundLog->getContext());
        $this->assertSame('api', $foundLog->getChannel());
        $this->assertSame('ApiController', $foundLog->getSource());
        $this->assertSame('user-789', $foundLog->getUserId());
        $this->assertSame('req-abc123', $foundLog->getRequestId());
        $this->assertSame('#0 test.php(10): method()', $foundLog->getStackTrace());
    }

    public function testFindOneByIdReturnsNullForNonExistent(): void
    {
        $foundLog = $this->repository->findOneById(999999);

        $this->assertNull($foundLog);
    }

    public function testFindByLevel(): void
    {
        $errorLog1 = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'Error 1'
        );
        $this->repository->save($errorLog1);

        $errorLog2 = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'Error 2'
        );
        $this->repository->save($errorLog2);

        $infoLog = new TenantLog(
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
        for ($i = 0; $i < 5; ++$i) {
            $log = new TenantLog(
                level: LogLevel::WARNING->value,
                message: 'Warning ' . $i
            );
            $this->repository->save($log);
        }

        $logs = $this->repository->findByLevel(LogLevel::WARNING->value, 2);

        $this->assertCount(2, $logs);
    }

    public function testFindByUserId(): void
    {
        $userId = 'user-test-' . uniqid();

        $log1 = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'User action 1',
            userId: $userId
        );
        $this->repository->save($log1);

        $log2 = new TenantLog(
            level: LogLevel::DEBUG->value,
            message: 'User action 2',
            userId: $userId
        );
        $this->repository->save($log2);

        $log3 = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'Different user action',
            userId: 'user-other'
        );
        $this->repository->save($log3);

        $logs = $this->repository->findByUserId($userId);

        $this->assertGreaterThanOrEqual(2, count($logs));

        foreach ($logs as $log) {
            $this->assertSame($userId, $log->getUserId());
        }
    }

    public function testFindByUserIdWithLimit(): void
    {
        $userId = 'user-limit-' . uniqid();

        for ($i = 0; $i < 5; ++$i) {
            $log = new TenantLog(
                level: LogLevel::DEBUG->value,
                message: 'Debug log ' . $i,
                userId: $userId
            );
            $this->repository->save($log);
        }

        $logs = $this->repository->findByUserId($userId, 3);

        $this->assertCount(3, $logs);
    }

    public function testFindByRequestId(): void
    {
        $requestId = 'req-trace-' . uniqid();

        // Simulate multiple log entries for a single request
        $log1 = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'Request started',
            requestId: $requestId
        );
        $this->repository->save($log1);

        usleep(1000); // Small delay

        $log2 = new TenantLog(
            level: LogLevel::DEBUG->value,
            message: 'Processing request',
            requestId: $requestId
        );
        $this->repository->save($log2);

        usleep(1000); // Small delay

        $log3 = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'Request failed',
            requestId: $requestId
        );
        $this->repository->save($log3);

        $log4 = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'Different request',
            requestId: 'req-different'
        );
        $this->repository->save($log4);

        $logs = $this->repository->findByRequestId($requestId);

        $this->assertCount(3, $logs);

        // Should be ordered by createdAt ASC for tracing
        $this->assertSame('Request started', $logs[0]->getMessage());
        $this->assertSame('Processing request', $logs[1]->getMessage());
        $this->assertSame('Request failed', $logs[2]->getMessage());

        foreach ($logs as $log) {
            $this->assertSame($requestId, $log->getRequestId());
        }
    }

    public function testFindByChannel(): void
    {
        $channel = 'payment_channel_' . uniqid();

        $log1 = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'Payment initiated',
            channel: $channel
        );
        $this->repository->save($log1);

        $log2 = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'Payment failed',
            channel: $channel
        );
        $this->repository->save($log2);

        $log3 = new TenantLog(
            level: LogLevel::INFO->value,
            message: 'Different channel log',
            channel: 'order_channel'
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
            $log = new TenantLog(
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
        $oldDate = new DateTime('-30 days');

        $deletedCount = $this->repository->deleteOlderThan($oldDate);

        $this->assertIsInt($deletedCount);
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testSaveWithComplexContext(): void
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
        ];

        $log = new TenantLog(
            level: LogLevel::ERROR->value,
            message: 'API request failed',
            context: $complexContext
        );

        $this->repository->save($log);

        $id = $log->getId();
        $this->assertNotNull($id);

        $foundLog = $this->repository->findOneById($id);

        $this->assertNotNull($foundLog);
        $this->assertSame($complexContext, $foundLog->getContext());
    }

    public function testSaveWithStackTrace(): void
    {
        $stackTrace = "#0 /app/src/Controller/ApiController.php(123): method()\n" .
            "#1 /app/src/Service/OrderService.php(456): process()\n" .
            '#2 {main}';

        $log = new TenantLog(
            level: LogLevel::CRITICAL->value,
            message: 'Critical error with stack trace',
            stackTrace: $stackTrace
        );

        $this->repository->save($log);

        $id = $log->getId();
        $this->assertNotNull($id);

        $foundLog = $this->repository->findOneById($id);

        $this->assertNotNull($foundLog);
        $this->assertSame($stackTrace, $foundLog->getStackTrace());
    }

    public function testRequestTracing(): void
    {
        $requestId = 'req-full-trace-' . uniqid();

        // Simulate a full request lifecycle
        $logs = [
            new TenantLog(LogLevel::INFO->value, 'Request received', requestId: $requestId),
            new TenantLog(LogLevel::DEBUG->value, 'Validating request', requestId: $requestId),
            new TenantLog(LogLevel::DEBUG->value, 'Processing order', requestId: $requestId),
            new TenantLog(LogLevel::WARNING->value, 'Low stock warning', requestId: $requestId),
            new TenantLog(LogLevel::INFO->value, 'Order created', requestId: $requestId),
            new TenantLog(LogLevel::INFO->value, 'Request completed', requestId: $requestId),
        ];

        foreach ($logs as $log) {
            $this->repository->save($log);
            usleep(1000); // Ensure different timestamps
        }

        $traceLogs = $this->repository->findByRequestId($requestId);

        $this->assertCount(6, $traceLogs);

        // Verify order (ASC by createdAt)
        $messages = array_map(fn($log) => $log->getMessage(), $traceLogs);
        $this->assertSame([
            'Request received',
            'Validating request',
            'Processing order',
            'Low stock warning',
            'Order created',
            'Request completed',
        ], $messages);
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        /** @var DoctrineTenantLogRepository $repository */
        $repository = self::getContainer()->get(DoctrineTenantLogRepository::class);
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }
}
