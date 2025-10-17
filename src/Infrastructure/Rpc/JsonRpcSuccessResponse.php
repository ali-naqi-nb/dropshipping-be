<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

final class JsonRpcSuccessResponse extends JsonRpcResponse
{
    private function __construct(
        string $id,
        private readonly mixed $result
    ) {
        parent::__construct($id);
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public static function fromRpcResult(string $id, RpcResult $rpcResult): self
    {
        return new self($id, $rpcResult->getResult());
    }
}
