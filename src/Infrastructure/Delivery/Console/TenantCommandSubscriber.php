<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Console;

use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @codeCoverageIgnore
 * TODO: find a way how this can be tested
 */
final class TenantCommandSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly DoctrineTenantConnection $connection,
        private readonly TenantServiceInterface $tenantService,
        private readonly array $allowedCommands = []
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ConsoleEvents::COMMAND => [['onCommand', 2052]]];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        /** @var Command $command */
        $command = $event->getCommand();
        if (!$this->isProperCommand($command)) {
            return;
        }

        $input = $event->getInput();
        $commandDefinition = $command->getDefinition();
        /** @var Application $application */
        $application = $command->getApplication();
        $applicationDefinition = $application->getDefinition();

        $tenantOption = new InputOption('tenant', null, InputOption::VALUE_OPTIONAL, 'Tenant id', null);
        $commandDefinition->addOption($tenantOption);

        $applicationDefinition->addOption($tenantOption);

        if (!$commandDefinition->hasOption('em')) {
            $emOption = new InputOption(
                'em',
                null,
                InputOption::VALUE_OPTIONAL,
                'The entity manager to use for this command'
            );
            $commandDefinition->addOption($emOption);
            $applicationDefinition->addOption($emOption);
        }
        $input->bind($commandDefinition);

        $tenantId = $input->getOption('tenant');

        if (null === $tenantId) {
            return;
        }

        $input->setOption('em', 'tenant');
        $commandDefinition->getOption('em')->setDefault('tenant');

        $dbConfig = $this->tenantService->getDbConfig($tenantId);

        if (null === $dbConfig) {
            throw new Exception(sprintf('Tenant identified as %s does not exists', $tenantId));
        }

        $this->connection->create($dbConfig);
    }

    private function isProperCommand(Command $command): bool
    {
        return in_array($command->getName(), $this->allowedCommands, true);
    }
}
