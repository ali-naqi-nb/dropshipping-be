<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\ExceptionRpcResultReceiver;

use App\Infrastructure\Rpc\RpcResult;
use Throwable;

interface ExceptionFactoryInterface
{
    public function fromResult(RpcResult $rpcResult): ?Throwable;
}
