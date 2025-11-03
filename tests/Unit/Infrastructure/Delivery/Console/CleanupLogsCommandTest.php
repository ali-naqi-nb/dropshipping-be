<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Delivery\Console;

use App\Domain\Model\Log\MainLogRepositoryInterface;
use App\Domain\Model\Log\TenantLogRepositoryInterface;
use App\Domain\Model\Tenant\DbConfig;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Infrastructure\Delivery\Console\CleanupLogsCommand;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Unit\UnitTestCase;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class CleanupLogsCommandTest extends UnitTestCase
{
    /**
     * @phpstan-var MockObject
     */
    private MockObject $mainLogRepository;

    /**
     * @phpstan-var MockObject
     */
    private MockObject $tenantLogRepository;

    /**
     * @phpstan-var MockObject
     */
    private MockObject $tenantService;

    /**
     * @phpstan-var MockObject
     */
    private MockObject $tenantConnection;

    /**
     * @phpstan-var MockObject
     */
    private MockObject $logger;

    private CleanupLogsCommand $command;
    private CommandTester $commandTester;

    public function testExecuteWithDefaultOptions(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->with($this->callback(function (DateTime $date) {
                $expected = new DateTime('-30 days');
                $expected->setTime(0, 0, 0);

                return abs($date->getTimestamp() - $expected->getTimestamp()) < 5;
            }))
            ->willReturn(100);

        $this->tenantService
            ->expects($this->once())
            ->method('getAll')
            ->with(0)
            ->willReturn([]);

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Log Cleanup', $output);
        $this->assertStringContainsString('Deleted 100 log entries from main namespace', $output);
        $this->assertStringContainsString('cleanup completed successfully', $output);
    }

    public function testExecuteWithCustomDays(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->with($this->callback(function (DateTime $date) {
                $expected = new DateTime('-60 days');
                $expected->setTime(0, 0, 0);

                return abs($date->getTimestamp() - $expected->getTimestamp()) < 5;
            }))
            ->willReturn(250);

        $this->tenantService
            ->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $exitCode = $this->commandTester->execute(['--days' => '60']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Deleted 250 log entries', $this->commandTester->getDisplay());
    }

    public function testExecuteWithSpecificDate(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->with($this->callback(function (DateTime $date) {
                return '2024-01-01' === $date->format('Y-m-d');
            }))
            ->willReturn(500);

        $this->tenantService
            ->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $exitCode = $this->commandTester->execute(['--date' => '2024-01-01']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Cutoff date: 2024-01-01', $this->commandTester->getDisplay());
    }

    public function testExecuteWithInvalidDateFormat(): void
    {
        $this->mainLogRepository
            ->expects($this->never())
            ->method('deleteOlderThan');

        $exitCode = $this->commandTester->execute(['--date' => 'invalid-date']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Invalid date format', $this->commandTester->getDisplay());
    }

    public function testExecuteWithInvalidDays(): void
    {
        $this->mainLogRepository
            ->expects($this->never())
            ->method('deleteOlderThan');

        $exitCode = $this->commandTester->execute(['--days' => '-10']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('must be a positive integer', $this->commandTester->getDisplay());
    }

    public function testExecuteWithSkipMain(): void
    {
        $this->mainLogRepository
            ->expects($this->never())
            ->method('deleteOlderThan');

        $this->tenantService
            ->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $exitCode = $this->commandTester->execute(['--skip-main' => true]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Skipping main namespace logs', $this->commandTester->getDisplay());
    }

    public function testExecuteWithSingleTenant(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->willReturn(50);

        $this->tenantService
            ->expects($this->never())
            ->method('getAll');

        $dbConfig = $this->createMock(DbConfig::class);
        $dbConfig->method('getTenantId')->willReturn('tenant-123');

        $this->tenantService
            ->expects($this->once())
            ->method('getDbConfig')
            ->with('tenant-123')
            ->willReturn($dbConfig);

        $this->tenantConnection
            ->expects($this->once())
            ->method('create')
            ->with($dbConfig);

        $this->tenantConnection
            ->expects($this->once())
            ->method('isConnected')
            ->willReturn(true);

        $this->tenantConnection
            ->expects($this->once())
            ->method('close');

        $this->tenantLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->willReturn(75);

        $exitCode = $this->commandTester->execute(['--tenant-id' => 'tenant-123']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Single tenant cleanup', $output);
        $this->assertStringContainsString('tenant-123', $output);
        $this->assertStringContainsString('Deleted 75 log entries', $output);
    }

    public function testExecuteWithMultipleTenants(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->willReturn(100);

        $tenant1 = $this->createMock(Tenant::class);
        $tenant1->method('getId')->willReturn('tenant-1');

        $tenant2 = $this->createMock(Tenant::class);
        $tenant2->method('getId')->willReturn('tenant-2');

        $dbConfig1 = $this->createMock(DbConfig::class);
        $dbConfig1->method('getTenantId')->willReturn('tenant-1');

        $dbConfig2 = $this->createMock(DbConfig::class);
        $dbConfig2->method('getTenantId')->willReturn('tenant-2');

        $this->tenantService
            ->expects($this->exactly(2))
            ->method('getAll')
            ->willReturnOnConsecutiveCalls(
                [$tenant1, $tenant2],
                []
            );

        $this->tenantService
            ->expects($this->exactly(2))
            ->method('getDbConfig')
            ->willReturnMap([
                ['tenant-1', $dbConfig1],
                ['tenant-2', $dbConfig2],
            ]);

        $this->tenantConnection
            ->expects($this->exactly(2))
            ->method('create');

        $this->tenantConnection
            ->expects($this->exactly(2))
            ->method('isConnected')
            ->willReturn(true);

        $this->tenantConnection
            ->expects($this->exactly(2))
            ->method('close');

        $this->tenantLogRepository
            ->expects($this->exactly(2))
            ->method('deleteOlderThan')
            ->willReturnOnConsecutiveCalls(50, 75);

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Tenant tenant-1: Deleted 50 log entries', $output);
        $this->assertStringContainsString('Tenant tenant-2: Deleted 75 log entries', $output);
        $this->assertStringContainsString('Total Tenants Processed', $output);
    }

    public function testExecuteWithTenantMissingDbConfig(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->willReturn(10);

        $this->tenantService
            ->expects($this->never())
            ->method('getAll');

        $this->tenantService
            ->expects($this->once())
            ->method('getDbConfig')
            ->with('tenant-no-config')
            ->willReturn(null);

        $this->tenantConnection
            ->expects($this->never())
            ->method('create');

        $this->tenantLogRepository
            ->expects($this->never())
            ->method('deleteOlderThan');

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                'Tenant database config not found during log cleanup',
                ['tenant_id' => 'tenant-no-config']
            );

        $exitCode = $this->commandTester->execute(['--tenant-id' => 'tenant-no-config']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Database config not found', $output);
        $this->assertStringContainsString('Failed Tenants', $output);
    }

    public function testExecuteWithTenantDeletionError(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->willReturn(20);

        $tenant1 = $this->createMock(Tenant::class);
        $tenant1->method('getId')->willReturn('tenant-error');

        $this->tenantService
            ->expects($this->exactly(2))
            ->method('getAll')
            ->willReturnOnConsecutiveCalls(
                [$tenant1],
                []
            );

        $dbConfig = $this->createMock(DbConfig::class);

        $this->tenantService
            ->expects($this->once())
            ->method('getDbConfig')
            ->willReturn($dbConfig);

        $this->tenantConnection
            ->expects($this->once())
            ->method('create')
            ->with($dbConfig);

        $this->tenantLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->willThrowException(new \Exception('Database connection failed'));

        $this->tenantConnection
            ->expects($this->once())
            ->method('isConnected')
            ->willReturn(true);

        $this->tenantConnection
            ->expects($this->once())
            ->method('close');

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Failed to delete tenant logs',
                $this->callback(function ($context) {
                    return 'tenant-error' === $context['tenant_id']
                        && 'Database connection failed' === $context['error'];
                })
            );

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to delete logs', $output);
        $this->assertStringContainsString('Database connection failed', $output);
    }

    public function testExecuteWithMainNamespaceDeletionError(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->willThrowException(new \Exception('Main DB error'));

        $this->tenantService
            ->expects($this->never())
            ->method('getAll');

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Failed to delete main namespace logs',
                $this->callback(function ($context) {
                    return 'Main DB error' === $context['error'];
                })
            );

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Failed to delete main namespace logs', $this->commandTester->getDisplay());
    }

    public function testExecuteSummaryTableContainsCorrectMetrics(): void
    {
        $this->mainLogRepository
            ->expects($this->once())
            ->method('deleteOlderThan')
            ->willReturn(100);

        $tenant1 = $this->createMock(Tenant::class);
        $tenant1->method('getId')->willReturn('tenant-1');

        $tenant2 = $this->createMock(Tenant::class);
        $tenant2->method('getId')->willReturn('tenant-2');

        $this->tenantService
            ->expects($this->exactly(2))
            ->method('getAll')
            ->willReturnOnConsecutiveCalls(
                [$tenant1, $tenant2],
                []
            );

        $dbConfig = $this->createMock(DbConfig::class);

        $this->tenantService
            ->method('getDbConfig')
            ->willReturn($dbConfig);

        $this->tenantConnection
            ->method('isConnected')
            ->willReturn(true);

        $this->tenantLogRepository
            ->expects($this->exactly(2))
            ->method('deleteOlderThan')
            ->willReturnOnConsecutiveCalls(50, 75);

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Cleanup Summary', $output);
        $this->assertStringContainsString('MAIN NAMESPACE', $output);
        $this->assertStringContainsString('TENANT NAMESPACE', $output);
        $this->assertStringContainsString('OVERALL', $output);
        $this->assertStringContainsString('Total Tenants Processed', $output);
        $this->assertStringContainsString('Successful Tenants', $output);
        $this->assertStringContainsString('Failed Tenants', $output);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mainLogRepository = $this->createMock(MainLogRepositoryInterface::class);
        $this->tenantLogRepository = $this->createMock(TenantLogRepositoryInterface::class);
        $this->tenantService = $this->createMock(TenantServiceInterface::class);
        $this->tenantConnection = $this->createMock(DoctrineTenantConnection::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->command = new CleanupLogsCommand(
            $this->mainLogRepository,
            $this->tenantLogRepository,
            $this->tenantService,
            $this->tenantConnection,
            $this->logger
        );

        $this->commandTester = new CommandTester($this->command);
    }
}
