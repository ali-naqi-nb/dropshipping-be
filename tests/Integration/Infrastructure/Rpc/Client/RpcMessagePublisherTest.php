<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\Client;

use App\Infrastructure\Rpc\Client\RpcMessageClientInterface;
use App\Infrastructure\Rpc\RpcMessage;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\RpcResultFactory;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

final class RpcMessagePublisherTest extends IntegrationTestCase
{
    private RpcMessageClientInterface $messagePublisher;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var RpcMessageClientInterface $messagePublisher */
        $messagePublisher = self::getContainer()->get(RpcMessageClientInterface::class);
        $this->messagePublisher = $messagePublisher;
    }

    public function testRequest(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            []
        );

        $this->messagePublisher->request($rpcMessage->method, $rpcMessage->arguments, $rpcMessage->onError, $rpcMessage->onSuccess);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async_rpc');
        $this->assertCount(1, $transport->getSent());
    }

    public function testReply(): void
    {
        $rpcMessage = new RpcMessage(
            'id',
            'method',
            [],
            onSuccess: 'success'
        );

        $this->messagePublisher->reply($rpcMessage->id, 'success', [RpcResultFactory::RESULT]);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async_rpc');
        $this->assertCount(1, $transport->getSent());
    }
}
