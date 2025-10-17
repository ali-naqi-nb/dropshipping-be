<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\Client;

use App\Infrastructure\Rpc\Client\RpcCommandClientInterface;
use App\Infrastructure\Rpc\RpcCommand;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\RpcResultFactory;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

final class RpcCommandClientTest extends IntegrationTestCase
{
    private RpcCommandClientInterface $commandClient;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var RpcCommandClientInterface $commandClient */
        $commandClient = self::getContainer()->get(RpcCommandClientInterface::class);
        $this->commandClient = $commandClient;
    }

    public function testCall(): void
    {
        $rpcResult = RpcResultFactory::getRpcCommandResult();

        $this->mockRpcResponse(
            function (RpcCommand $rpcCommand) {
                return 'test.ping' === $rpcCommand->getCommand();
            },
            $rpcResult,
        );

        $result = $this->commandClient->call('test', 'ping', arguments: [], timeout: 5);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.sync_rpc');
        $this->assertCount(1, $transport->getSent());

        $this->assertSame($rpcResult, $result);
    }
}
