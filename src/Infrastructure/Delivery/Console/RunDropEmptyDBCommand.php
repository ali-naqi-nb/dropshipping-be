<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Console;

use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(name: 'migrations:drop-empty', description: 'Drop dropshipping db when Tenant dbConfiguredAt is null', hidden: false)]
final class RunDropEmptyDBCommand extends Command
{
    public function __construct(
        private readonly TenantServiceInterface $tenantService,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $schemaManager = $this->connection->createSchemaManager();
        $serviceName = 'dropshipping';
        $chunk = 0;
        do {
            $tenants = $this->tenantService->getAllWithNullDbConfiguredAt($chunk++);

            /** @var Tenant $tenant */
            foreach ($tenants as $tenant) {
                $database = str_replace('-', '_', strtolower($serviceName.'_'.$tenant->getId()));

                try {
                    $schemaManager->dropDatabase($database);
                    $this->tenantService->removeTenantById($tenant);
                } catch (Throwable $exception) {
                    $this->logger->error($exception->getMessage());
                    continue;
                }
            }
        } while (!empty($tenants));

        return Command::SUCCESS;
    }
}
