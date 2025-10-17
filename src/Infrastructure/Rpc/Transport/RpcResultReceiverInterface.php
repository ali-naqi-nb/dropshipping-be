<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport;

use App\Infrastructure\Rpc\Exception\RpcException;
use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;

interface RpcResultReceiverInterface
{
    /**
     * @throws RpcException
     * @throws TimeoutException
     */
    public function receive(RpcCommand $command): RpcResult;
}
