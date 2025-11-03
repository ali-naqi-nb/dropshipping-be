<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Console;

use App\Domain\Model\Log\MainLogRepositoryInterface;
use App\Domain\Model\Log\TenantLogRepositoryInterface;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Console command to clean up old log entries from both main and tenant databases.
 *
 * This command deletes log entries older than a specified date from the periodic_logs
 * table in both the main namespace and all tenant namespaces.
 *
 * Usage:
 *   php bin/console app:cleanup-logs
 *   php bin/console app:cleanup-logs --days=60
 *   php bin/console app:cleanup-logs --date=2024-01-01
 *   php bin/console app:cleanup-logs --tenant-id=tenant-123
 */
#[AsCommand(
    name: 'app:cleanup-logs',
    description: 'Delete log entries older than a specific date (default: 30 days)'
)]
final class CleanupLogsCommand extends Command
{
    private const DEFAULT_DAYS = 30;

    public function __construct(
        private readonly MainLogRepositoryInterface   $mainLogRepository,
        private readonly TenantLogRepositoryInterface $tenantLogRepository,
        private readonly TenantServiceInterface       $tenantService,
        private readonly DoctrineTenantConnection     $tenantConnection,
        private readonly LoggerInterface              $logger,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'days',
                'd',
                InputOption::VALUE_REQUIRED,
                sprintf('Delete logs older than this many days (default: %d)', self::DEFAULT_DAYS),
                null
            )
            ->addOption(
                'date',
                null,
                InputOption::VALUE_REQUIRED,
                'Delete logs older than this specific date (format: YYYY-MM-DD). Overrides --days option.',
                null
            )
            ->addOption(
                'tenant-id',
                't',
                InputOption::VALUE_REQUIRED,
                'Clean up logs only for a specific tenant by ID (omit to clean all tenants)',
                null
            )
            ->addOption(
                'skip-main',
                null,
                InputOption::VALUE_NONE,
                'Skip cleanup of main namespace logs'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $cutoffDate = $this->determineCutoffDate($input, $io);
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $tenantId = $input->getOption('tenant-id');
        $skipMain = $input->getOption('skip-main');

        $io->title('Log Cleanup');
        $io->note(sprintf('Cutoff date: %s', $cutoffDate->format('Y-m-d H:i:s')));
        $io->note(sprintf('Logs older than this date will be deleted.'));

        if ($tenantId) {
            $io->note(sprintf('Mode: Single tenant cleanup (Tenant ID: %s)', $tenantId));
        } else {
            $io->note('Mode: All tenants cleanup');
        }

        if ($skipMain) {
            $io->warning('Skipping main namespace logs cleanup');
        }

        $totalDeletedMain = 0;
        $totalDeletedTenant = 0;
        $successfulTenants = 0;
        $failedTenants = 0;
        $totalTenantsProcessed = 0;

        if (!$skipMain) {
            $io->section('Cleaning main namespace logs');
            try {
                $deletedCount = $this->mainLogRepository->deleteOlderThan($cutoffDate);
                $totalDeletedMain = $deletedCount;
                $io->success(sprintf('Deleted %d log entries from main namespace', $deletedCount));
            } catch (Throwable $e) {
                $io->error(sprintf('Failed to delete main namespace logs: %s', $e->getMessage()));
                $this->logger->error('Failed to delete main namespace logs', [
                    'error' => $e->getMessage(),
                    'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
                ]);

                return Command::FAILURE;
            }
        }

        $io->section('Cleaning tenant namespace logs');

        if ($tenantId) {
            $result = $this->cleanupTenantLogs($tenantId, $cutoffDate, $io);
            $totalTenantsProcessed = 1;
            if ($result['success']) {
                $successfulTenants = 1;
                $totalDeletedTenant = $result['deleted'];
            } else {
                $failedTenants = 1;
            }
        } else {
            $chunk = 0;
            do {
                $tenants = $this->tenantService->getAll($chunk++);

                foreach ($tenants as $tenant) {
                    ++$totalTenantsProcessed;
                    $result = $this->cleanupTenantLogs($tenant->getId(), $cutoffDate, $io);

                    if ($result['success']) {
                        ++$successfulTenants;
                        $totalDeletedTenant += $result['deleted'];
                    } else {
                        ++$failedTenants;
                    }
                }
            } while (!empty($tenants));
        }

        $io->section('Cleanup Summary');
        $io->table(
            ['Metric', 'Count'],
            [
                ['--- MAIN NAMESPACE ---', ''],
                ['Logs Deleted', $skipMain ? 'Skipped' : $totalDeletedMain],
                ['', ''],
                ['--- TENANT NAMESPACE ---', ''],
                ['Total Tenants Processed', $totalTenantsProcessed],
                ['Successful Tenants', $successfulTenants],
                ['Failed Tenants', $failedTenants],
                ['Total Logs Deleted', $totalDeletedTenant],
                ['', ''],
                ['--- OVERALL ---', ''],
                ['Total Logs Deleted', $skipMain ? $totalDeletedTenant : ($totalDeletedMain + $totalDeletedTenant)],
            ]
        );

        if ($failedTenants > 0) {
            $io->warning(sprintf('%d tenant(s) failed during cleanup. Check logs for details.', $failedTenants));

            return Command::FAILURE;
        }

        $io->success('Log cleanup completed successfully');

        return Command::SUCCESS;
    }

    /**
     * Determine the cutoff date based on command options.
     *
     * @throws \Exception
     */
    private function determineCutoffDate(InputInterface $input, SymfonyStyle $io): DateTime
    {
        $dateOption = $input->getOption('date');
        $daysOption = $input->getOption('days');

        if ($dateOption) {
            $cutoffDate = DateTime::createFromFormat('Y-m-d', $dateOption);
            if (!$cutoffDate) {
                throw new \Exception('Invalid date format. Expected format: YYYY-MM-DD');
            }
            $cutoffDate->setTime(0, 0, 0);

            return $cutoffDate;
        }

        $days = $daysOption ? (int)$daysOption : self::DEFAULT_DAYS;

        if ($days <= 0) {
            throw new \Exception('The --days option must be a positive integer');
        }

        $cutoffDate = new DateTime();
        $cutoffDate->modify(sprintf('-%d days', $days));
        $cutoffDate->setTime(0, 0, 0);

        return $cutoffDate;
    }

    /**
     * Clean up logs for a specific tenant.
     *
     * @return array{success: bool, deleted: int}
     */
    private function cleanupTenantLogs(string $tenantId, DateTime $cutoffDate, SymfonyStyle $io): array
    {
        try {
            $dbConfig = $this->tenantService->getDbConfig($tenantId);

            if (null === $dbConfig) {
                $io->warning(sprintf('Tenant %s: Database config not found, skipping', $tenantId));
                $this->logger->warning('Tenant database config not found during log cleanup', [
                    'tenant_id' => $tenantId,
                ]);

                return ['success' => false, 'deleted' => 0];
            }

            $this->tenantConnection->create($dbConfig);

            $deletedCount = $this->tenantLogRepository->deleteOlderThan($cutoffDate);

            $io->text(sprintf('Tenant %s: Deleted %d log entries', $tenantId, $deletedCount));

            $this->logger->info('Tenant logs cleaned up successfully', [
                'tenant_id' => $tenantId,
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
            ]);

            return ['success' => true, 'deleted' => $deletedCount];
        } catch (Throwable $e) {
            $io->error(sprintf('Tenant %s: Failed to delete logs: %s', $tenantId, $e->getMessage()));
            $this->logger->error('Failed to delete tenant logs', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
            ]);

            return ['success' => false, 'deleted' => 0];
        } finally {
            if ($this->tenantConnection->isConnected()) {
                $this->tenantConnection->close();
            }
        }
    }
}
