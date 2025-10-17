<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\ExceptionRpcResultReceiver;

use App\Infrastructure\Rpc\Exception\CommandNotFoundException;
use App\Infrastructure\Rpc\Exception\InternalServerErrorException;
use App\Infrastructure\Rpc\Exception\InvalidParametersException;
use App\Infrastructure\Rpc\Exception\InvalidRequestException;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\RpcResultStatus;
use Throwable;

final class ExceptionFactory implements ExceptionFactoryInterface
{
    public function fromResult(RpcResult $rpcResult): ?Throwable
    {
        if (RpcResultStatus::ERROR !== $rpcResult->getStatus()) {
            return null;
        }

        $code = $rpcResult->getResult()['code'] ?? 0;
        $message = $rpcResult->getResult()['message'] ?? '';
        $data = $rpcResult->getResult()['data'] ?? [];

        return match ($code) {
            CommandNotFoundException::CODE => new CommandNotFoundException($message),
            InvalidParametersException::CODE => new InvalidParametersException($message),
            InternalServerErrorException::CODE => new InternalServerErrorException($message),
            InvalidRequestException::CODE => new InvalidRequestException($message, $data),
            default => null,
        };
    }
}
