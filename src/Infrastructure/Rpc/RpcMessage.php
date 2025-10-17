<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

final class RpcMessage
{
    public function __construct(
        public readonly string $id,
        public readonly string $method,
        public readonly array $arguments,
        public readonly ?string $onError = null,
        public readonly ?string $onSuccess = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'arguments' => $this->arguments,
            'onError' => $this->onError,
            'onSuccess' => $this->onSuccess,
        ];
    }
}
