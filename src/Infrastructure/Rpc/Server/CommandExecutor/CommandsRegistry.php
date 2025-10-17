<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Server\CommandExecutor;

use App\Infrastructure\Rpc\Exception\CommandNotFoundException;

final class CommandsRegistry
{
    public function __construct(protected array $map = [])
    {
        $this->map = array_change_key_case($this->map, CASE_LOWER);
    }

    public function addController(string $command, object $object, string $method): void
    {
        $this->map[strtolower($command)] = [$object, $method];
    }

    public function getController(string $command): array
    {
        if (!$this->commandExists($command)) {
            throw new CommandNotFoundException(sprintf('Command "%s" not found', $command));
        }

        return $this->map[strtolower($command)];
    }

    private function commandExists(string $command): bool
    {
        return array_key_exists(strtolower($command), $this->map);
    }
}
