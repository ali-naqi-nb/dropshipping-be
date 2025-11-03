<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Client;

interface RpcMessageClientInterface
{
    public function request(string $requestMethod, array $arguments, ?string $onError = null, ?string $onSuccess = null): void;

    public function reply(string $id, string $replyMethod, array $result): void;
}
