<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Client;

use App\Infrastructure\Rpc\RpcMessage;
use App\Infrastructure\Rpc\Service\CallIdGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

final class RpcMessageClient implements RpcMessageClientInterface
{
    public function __construct(
        private readonly CallIdGeneratorInterface $rpcMessageIdGenerator,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function request(string $requestMethod, array $arguments, ?string $onError = null, ?string $onSuccess = null): void
    {
        $message = new RpcMessage(
            id: $this->rpcMessageIdGenerator->generate(),
            method: $requestMethod,
            arguments: $arguments,
            onError: $onError,
            onSuccess: $onSuccess,
        );
        $this->bus->dispatch($message, [new AmqpStamp(routingKey: $message->method, flags: 0)]);

        $this->logger->debug('RpcMessage request sent', $message->toArray());
    }

    public function reply(string $id, string $replyMethod, array $result): void
    {
        $message = new RpcMessage(
            id: $id,
            method: $replyMethod,
            arguments: $result,
        );
        $this->bus->dispatch($message, [new AmqpStamp(routingKey: $message->method, flags: 0)]);

        $this->logger->debug('RpcMessage reply sent', $message->toArray());
    }
}
