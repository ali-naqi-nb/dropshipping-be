<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Delivery\Console;

use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsCommand as AppCommand;
use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsCommandHandler;
use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsResult;
use App\Infrastructure\Delivery\Console\SyncAliExpressProductsCommand;
use App\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class SyncAliExpressProductsCommandTest extends UnitTestCase
{
    /**
     * @phpstan-var MockObject
     */
    private MockObject $handler;
    private SyncAliExpressProductsCommand $command;
    private CommandTester $commandTester;

    public function testExecuteWithDefaultOptions(): void
    {
        $result = new SyncAliExpressProductsResult();
        $result->incrementSuccessfulTenants();
        $result->incrementSuccessfulProducts();
        $result->incrementVariantsUpdated();

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (AppCommand $command) {
                return false === $command->isDryRun()
                    && null === $command->getTenantId()
                    && AppCommand::DEFAULT_TIMEOUT_MINUTES === $command->getTimeoutMinutes();
            }))
            ->willReturn($result);

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Sync completed successfully', $this->commandTester->getDisplay());
    }

    public function testExecuteWithDryRun(): void
    {
        $result = new SyncAliExpressProductsResult();

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (AppCommand $command) {
                return true === $command->isDryRun();
            }))
            ->willReturn($result);

        $exitCode = $this->commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('DRY RUN MODE', $this->commandTester->getDisplay());
    }

    public function testExecuteWithTenantId(): void
    {
        $result = new SyncAliExpressProductsResult();

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (AppCommand $command) {
                return 'test-tenant-123' === $command->getTenantId();
            }))
            ->willReturn($result);

        $exitCode = $this->commandTester->execute(['--tenant-id' => 'test-tenant-123']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Single tenant sync', $this->commandTester->getDisplay());
        $this->assertStringContainsString('test-tenant-123', $this->commandTester->getDisplay());
    }

    public function testExecuteWithCustomTimeout(): void
    {
        $result = new SyncAliExpressProductsResult();

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (AppCommand $command) {
                return 60 === $command->getTimeoutMinutes();
            }))
            ->willReturn($result);

        $exitCode = $this->commandTester->execute(['--timeout' => '60']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Timeout: 60 minutes', $this->commandTester->getDisplay());
    }

    public function testExecuteWithInvalidTimeout(): void
    {
        $this->handler
            ->expects($this->never())
            ->method('__invoke');

        $exitCode = $this->commandTester->execute(['--timeout' => 'invalid']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('must be a positive integer', $this->commandTester->getDisplay());
    }

    public function testExecuteWithNegativeTimeout(): void
    {
        $this->handler
            ->expects($this->never())
            ->method('__invoke');

        $exitCode = $this->commandTester->execute(['--timeout' => '-10']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('must be a positive integer', $this->commandTester->getDisplay());
    }

    public function testExecuteWithZeroTimeout(): void
    {
        $this->handler
            ->expects($this->never())
            ->method('__invoke');

        $exitCode = $this->commandTester->execute(['--timeout' => '0']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('must be a positive integer', $this->commandTester->getDisplay());
    }

    public function testExecuteShowsParallelModeWhenNoTenantId(): void
    {
        $result = new SyncAliExpressProductsResult();

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($result);

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Parallel sync (all tenants)', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Concurrency: 10 tenants', $this->commandTester->getDisplay());
    }

    public function testExecuteDisplaysResultMetrics(): void
    {
        $result = new SyncAliExpressProductsResult();
        $result->incrementSuccessfulTenants();
        $result->incrementSuccessfulTenants();
        $result->incrementFailedTenants();
        $result->incrementSkippedTenants();
        $result->incrementSuccessfulProducts();
        $result->incrementVariantsUpdated();

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($result);

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Total Tenants Processed', $output);
        $this->assertStringContainsString('Successful Tenants', $output);
        $this->assertStringContainsString('Failed Tenants', $output);
        $this->assertStringContainsString('Skipped Tenants', $output);
        $this->assertStringContainsString('Total Products Processed', $output);
        $this->assertStringContainsString('Variants Updated', $output);
    }

    public function testExecuteHandlesException(): void
    {
        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new \Exception('Test error message'));

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Sync failed', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Test error message', $this->commandTester->getDisplay());
    }

    public function testExecuteWithAllOptionsCombined(): void
    {
        $result = new SyncAliExpressProductsResult();

        $this->handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (AppCommand $command) {
                return true === $command->isDryRun()
                    && 'tenant-456' === $command->getTenantId()
                    && 45 === $command->getTimeoutMinutes();
            }))
            ->willReturn($result);

        $exitCode = $this->commandTester->execute([
            '--tenant-id' => 'tenant-456',
            '--timeout' => '45',
            '--dry-run' => true,
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->createMock(SyncAliExpressProductsCommandHandler::class);
        $this->command = new SyncAliExpressProductsCommand($this->handler);
        $this->commandTester = new CommandTester($this->command);
    }
}
