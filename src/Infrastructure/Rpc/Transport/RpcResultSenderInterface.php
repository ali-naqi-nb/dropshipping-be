<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport;

use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;

interface RpcResultSenderInterface
{
    public function send(RpcCommand $command, RpcResult $result): void;
}
