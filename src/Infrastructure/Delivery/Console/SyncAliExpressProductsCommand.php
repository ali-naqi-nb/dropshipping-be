<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Console;

use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsCommand as AppCommand;
use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsCommandHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to sync stock and supplier prices from AliExpress.
 *
 * This command is designed to be run daily via cron or a scheduler.
 *
 * Usage:
 *   php bin/console app:sync-aliexpress-products
 *   php bin/console app:sync-aliexpress-products --limit=10 --dry-run
 */
#[AsCommand(
    name: 'app:sync-aliexpress-products',
    description: 'Sync stock and supplier prices from AliExpress for all imported products'
)]
final class SyncAliExpressProductsCommand extends Command
{
    public function __construct(
        private readonly SyncAliExpressProductsCommandHandler $handler,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Perform validation and logging but do not persist changes'
            )
            ->addOption(
                'tenant-id',
                't',
                InputOption::VALUE_REQUIRED,
                'Sync only a specific tenant by ID (omit to sync all tenants in parallel)',
                null
            )
            ->addOption(
                'timeout',
                null,
                InputOption::VALUE_REQUIRED,
                'Timeout in minutes for each tenant sync (default: 30 minutes)',
                AppCommand::DEFAULT_TIMEOUT_MINUTES
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dryRun = $input->getOption('dry-run');
        $tenantId = $input->getOption('tenant-id');
        $timeout = $input->getOption('timeout');

        if (!is_numeric($timeout) || (int)$timeout <= 0) {
            $io->error('The --timeout option must be a positive integer (minutes).');

            return Command::FAILURE;
        }

        $io->title('AliExpress Product Sync');

        if (null !== $tenantId) {
            $io->note(sprintf('Mode: Single tenant sync'));
            $io->note(sprintf('Tenant ID: %s', $tenantId));
        } else {
            $io->note(sprintf('Mode: Parallel sync (all tenants)'));
            $io->note(sprintf('Concurrency: %d tenants at a time', AppCommand::DEFAULT_CONCURRENCY));
        }

        $io->note(sprintf('Timeout: %d minutes per tenant', $timeout));

        if ($dryRun) {
            $io->warning('DRY RUN MODE - No changes will be persisted');
        }

        $io->text('Starting sync...');

        try {
            $command = new AppCommand(
                dryRun: (bool)$dryRun,
                tenantId: $tenantId,
                timeoutMinutes: (int)$timeout,
            );

            $result = ($this->handler)($command);

            $io->success('Sync completed successfully');

            $io->table(
                ['Metric', 'Count'],
                [
                    ['--- TENANTS ---', ''],
                    ['Total Tenants Processed', $result->getTotalTenantsProcessed()],
                    ['Successful Tenants', $result->getSuccessfulTenants()],
                    ['Failed Tenants', $result->getFailedTenants()],
                    ['Skipped Tenants (no products)', $result->getSkippedTenants()],
                    ['', ''],
                    ['--- PRODUCTS ---', ''],
                    ['Total Products Processed', $result->getTotalProductsProcessed()],
                    ['Successful Products', $result->getSuccessfulProducts()],
                    ['Failed Products', $result->getFailedProducts()],
                    ['', ''],
                    ['--- VARIANTS ---', ''],
                    ['Variants Updated', $result->getVariantsUpdated()],
                    ['Variants Skipped (no changes)', $result->getVariantsSkipped()],
                    ['Variants with Errors', $result->getVariantsWithErrors()],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Sync failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
