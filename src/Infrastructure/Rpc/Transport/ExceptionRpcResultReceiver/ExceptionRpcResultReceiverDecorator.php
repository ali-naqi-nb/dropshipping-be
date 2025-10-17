<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\ExceptionRpcResultReceiver;

use App\Infrastructure\Rpc\Exception\RpcException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\Transport\RpcResultReceiverInterface;
use Throwable;

final class ExceptionRpcResultReceiverDecorator implements RpcResultReceiverInterface
{
    public function __construct(
        private readonly RpcResultReceiverInterface $decoratedResultReceiver,
        private readonly ExceptionFactoryInterface $exceptionFactory,
    ) {
    }

    /**
     * @throws Throwable
     * @throws RpcException
     */
    public function receive(RpcCommand $command): RpcResult
    {
        $rpcResult = $this->decoratedResultReceiver->receive($command);
        $exception = $this->exceptionFactory->fromResult($rpcResult);

        if (null !== $exception) {
            throw $exception;
        }

        return $rpcResult;
    }
}
