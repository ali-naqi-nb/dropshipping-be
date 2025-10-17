<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Console;

use App\Domain\Model\Tenant\TenantServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 * TODO: Find a way to write meaningful tests about it
 */
#[AsCommand(name: 'tenants:migrations:migrate', description: 'Run migrations on all tenants.', hidden: false)]
final class RunTenantsMigrationsCommand extends Command
{
    public function __construct(private readonly TenantServiceInterface $tenantService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $chunk = 0;
        do {
            $tenants = $this->tenantService->getAll($chunk++);
            foreach ($tenants as $tenant) {
                $this->tenantService->executeDbMigrations($tenant->getId());
            }
        } while (!empty($tenants));

        return Command::SUCCESS;
    }
}
