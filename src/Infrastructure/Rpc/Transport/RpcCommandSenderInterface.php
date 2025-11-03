<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport;

use App\Infrastructure\Rpc\RpcCommand;

interface RpcCommandSenderInterface
{
    public function send(RpcCommand $command): void;
}
