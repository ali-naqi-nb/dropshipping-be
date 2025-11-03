<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

use App\Infrastructure\Rpc\Exception\RpcException;

final class JsonRpcErrorResponse extends JsonRpcResponse
{
    private function __construct(
        string $id,
        private readonly JsonRpcError $error
    ) {
        parent::__construct($id);
    }

    public function getError(): JsonRpcError
    {
        return $this->error;
    }

    public static function fromRpcException(string $id, RpcException $rpcException): self
    {
        return new self($id, JsonRpcError::fromRpcException($rpcException));
    }
}
