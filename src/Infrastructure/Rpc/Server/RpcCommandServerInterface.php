<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Server;

use App\Infrastructure\Rpc\RpcCommand;

interface RpcCommandServerInterface
{
    public function handle(RpcCommand $command): void;
}
