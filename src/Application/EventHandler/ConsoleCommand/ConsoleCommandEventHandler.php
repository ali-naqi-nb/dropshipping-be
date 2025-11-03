<?php

declare(strict_types=1);

namespace App\Application\EventHandler\ConsoleCommand;

use App\Domain\Model\ConsoleCommand\ConsoleCommandEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;

#[AsMessageHandler]
final class ConsoleCommandEventHandler
{
    private const SERVICE = 'dropshipping-manager';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ConsoleCommandEvent $event): void
    {
        if (self::SERVICE !== $event->getService()) {
            return;
        }

        $this->logger->info('Received a new console command', $event->toArray());

        $command = $event->getCommand();
        $arguments = $event->getArguments() ?? [];

        $process = new Process(['php', 'bin/console', $command, ...$arguments]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->error(sprintf('Error executing console command with output: %s', $process->getErrorOutput()), $event->toArray());

            return;
        }

        $this->logger->info(sprintf('Console command execution successful with output: %s', $process->getOutput()), $event->toArray());
    }
}
