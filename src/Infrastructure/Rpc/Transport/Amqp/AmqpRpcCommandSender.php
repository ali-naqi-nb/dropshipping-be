<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\Amqp;

use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\RpcCommandSenderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

final class AmqpRpcCommandSender implements RpcCommandSenderInterface
{
    public function __construct(
        private readonly MessageBusInterface $rpcBus,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function send(RpcCommand $command): void
    {
        $now = $this->clock->now()->getTimestamp();
        $secondsToTimeout = $command->getTimeoutAt() - $now;

        if ($secondsToTimeout <= 0) {
            $this->logger->debug('AmqpRpcCommandSender: the command has timed out before sending the command');

            throw new TimeoutException();
        }

        $millisecondsToTimeout = $secondsToTimeout * 1000;

        $this->rpcBus->dispatch($command, [
            new AmqpStamp(routingKey: $command->getCommand(), flags: 0, attributes: [
                'expiration' => $millisecondsToTimeout,
            ]),
        ]);
    }
}
