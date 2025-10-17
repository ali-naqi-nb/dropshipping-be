<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

final class JsonRpcRequest
{
    public function __construct(
        private readonly string $jsonrpc,
        private readonly string $method,
        private readonly ?array $params,
        private readonly ?string $id,
    ) {
    }

    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
