<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Client;

use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Rpc\Exception\RpcException;
use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\Service\CallIdGeneratorInterface;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\RpcCommandSenderInterface;
use App\Infrastructure\Rpc\Transport\RpcResultReceiverInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

final class RpcCommandClient implements RpcCommandClientInterface
{
    public function __construct(
        private readonly RpcCommandSenderInterface $commandSender,
        private readonly RpcResultReceiverInterface $resultReceiver,
        private readonly CallIdGeneratorInterface $idGenerator,
        private readonly TenantStorageInterface $tenantStorage,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws RpcException
     * @throws TimeoutException
     */
    public function call(
        string $service,
        string $command,
        array $arguments = [],
        int $timeout = self::DEFAULT_TIMEOUT,
    ): RpcResult {
        if ($timeout <= 0) {
            throw new InvalidArgumentException('Timeout must be greater than 0');
        }

        $currentTimestamp = $this->clock->now()->getTimestamp();
        $timeoutAt = $currentTimestamp + $timeout;

        $rpcCommand = new RpcCommand(
            sentAt: $currentTimestamp,
            timeoutAt: $timeoutAt,
            commandId: $this->idGenerator->generate(),
            command: "$service.$command",
            arguments: $arguments,
            tenantId: $this->tenantStorage->getId(),
        );

        $this->logger->debug('RPCClient: sending a command', [
            'commandId' => $rpcCommand->getCommandId(),
            'command' => $rpcCommand->getCommand(),
            'arguments' => $rpcCommand->getArguments(),
            'sentAt' => $rpcCommand->getSentAt(),
            'timeoutAt' => $rpcCommand->getTimeoutAt(),
            'tenantId' => $rpcCommand->getTenantId(),
        ]);

        $this->commandSender->send($rpcCommand);

        $this->logger->debug('RPCClient: the command has been sent');

        $result = $this->resultReceiver->receive($rpcCommand);

        $this->logger->debug('RPCClient: received a result', [
            'status' => $result->getStatus(),
            'executedAt' => $result->getExecutedAt(),
            'result' => $result->getResult(),
        ]);

        return $result;
    }
}
