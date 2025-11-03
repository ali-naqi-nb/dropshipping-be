<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Rpc\Client\RpcCommandClientInterface;
use App\Infrastructure\Rpc\RpcResult;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Trait\RpcTestBootTrait;
use ReflectionException;

abstract class RpcFunctionalTestCase extends IntegrationTestCase
{
    use RpcTestBootTrait;

    protected const SERVICE = 'dropshipping';
    protected const COMMAND = 'ping';

    protected RpcCommandClientInterface $client;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var RpcCommandClientInterface $client */
        $client = self::getContainer()->get(RpcCommandClientInterface::class);
        $this->client = $client;
    }

    /**
     * @throws ReflectionException
     */
    protected function call(array $arguments = [], int $timeout = RpcCommandClientInterface::DEFAULT_TIMEOUT): RpcResult
    {
        $this->emulateRpcCommand(static::SERVICE, static::COMMAND, $arguments);

        return $this->client->call(static::SERVICE, static::COMMAND, $arguments, $timeout);
    }
}
