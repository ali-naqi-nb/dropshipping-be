<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Console;

use App\Domain\Model\Tenant\ShopStatus;
use App\Domain\Model\Tenant\TenantServiceInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(name: 'tenants:parallel-migrations:migrate', description: 'Run parallel migrations on all tenants.', hidden: false)]
final class RunTenantsParallelMigrationsCommand extends Command
{
    protected static $defaultName = 'tenants:parallel-migrations:migrate';

    public function __construct(
        private readonly TenantServiceInterface $tenantService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption('statuses', null, InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Array of statuses')
            ->addOption('chunk', null, InputArgument::OPTIONAL, 'Chunk size')
            ->addOption('concurrency', null, InputArgument::OPTIONAL, 'Concurrency');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '2G');
        $arguments = $input->getOption('statuses');
        $chunkSize = $this->validateChunkSize($input->getOption('chunk'));
        $concurrency = $this->validateConcurrency($input->getOption('concurrency'));

        $chunk = 0;

        if (!empty($arguments)) {
            $argumentsArray = $this->validateStatuses($arguments);

            do {
                $tenants = $this->tenantService->getTenantsByStatus($chunk++, $argumentsArray, $chunkSize);
                $this->tenantService->executeParallelDbMigrations($tenants, $concurrency);
            } while (!empty($tenants));
        } else {
            do {
                $tenants = $this->tenantService->getAll($chunk++, $chunkSize);
                $this->tenantService->executeParallelDbMigrations($tenants, $concurrency);
            } while (!empty($tenants));
        }

        return Command::SUCCESS;
    }

    private function validateChunkSize(mixed $chunkSize): int
    {
        return (!empty((int) $chunkSize)) ? (int) $chunkSize : TenantServiceInterface::CHUNK_SIZE;
    }

    private function validateConcurrency(mixed $concurrency): int
    {
        return (!empty((int) $concurrency)) ? (int) $concurrency : TenantServiceInterface::CONCURRENCY;
    }

    private function validateStatuses(mixed $arguments): array
    {
        $allStatuses = ShopStatus::toArray();

        $argumentsArray = explode(',', $arguments);

        foreach ($argumentsArray as $argument) {
            if (!in_array($argument, $allStatuses)) {
                throw new InvalidArgumentException(sprintf('Invalid argument: %s', $argument));
            }
        }

        return $argumentsArray;
    }
}
