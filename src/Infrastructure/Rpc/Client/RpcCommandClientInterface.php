<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Client;

use App\Infrastructure\Rpc\RpcResult;

interface RpcCommandClientInterface
{
    public const DEFAULT_TIMEOUT = 60;

    /**
     * @param string $service   the service name, for example 'products'
     * @param string $command   the command name, for example 'createProduct'
     * @param array  $arguments the arguments for the command, both positional and named arguments are supported, for example ['id' => 1, 'name' => 'test'], [1, 'test']
     * @param int    $timeout   time in seconds to wait for the response
     */
    public function call(
        string $service,
        string $command,
        array $arguments = [],
        int $timeout = self::DEFAULT_TIMEOUT,
    ): RpcResult;
}
