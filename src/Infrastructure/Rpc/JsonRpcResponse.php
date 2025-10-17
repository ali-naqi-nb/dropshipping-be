<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

abstract class JsonRpcResponse
{
    protected function __construct(
        protected readonly string $id,
        protected readonly string $jsonrpc = '2.0',
    ) {
    }

    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
