<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

use App\Infrastructure\Rpc\Exception\InvalidRequestException;
use App\Infrastructure\Rpc\Exception\RpcException;

final class JsonRpcError
{
    private function __construct(
        private readonly int $code,
        private readonly string $message,
        private readonly mixed $data = null,
    ) {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public static function fromRpcException(RpcException $rpcException): self
    {
        $data = null;

        if ($rpcException instanceof InvalidRequestException) {
            $data = $rpcException->getData();
        }

        return new self($rpcException->getCode(), $rpcException->getMessage(), $data);
    }
}
