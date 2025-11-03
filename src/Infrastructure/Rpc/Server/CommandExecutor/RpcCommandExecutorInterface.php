<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Server\CommandExecutor;

use App\Infrastructure\Rpc\Exception\CommandNotFoundException;
use App\Infrastructure\Rpc\Exception\InvalidParametersException;

interface RpcCommandExecutorInterface
{
    /**
     * @throws CommandNotFoundException
     * @throws InvalidParametersException
     */
    public function execute(string $id, string $command, array $arguments): mixed;
}
