<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Server;

use App\Infrastructure\Rpc\Exception\ClientException;
use App\Infrastructure\Rpc\Exception\CommandNotFoundException;
use App\Infrastructure\Rpc\Exception\InvalidParametersException;
use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Infrastructure\Rpc\Server\CommandExecutor\RpcCommandExecutorInterface;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\RpcResultSenderInterface;
use Psr\Log\LoggerInterface;

final class RpcCommandServer implements RpcCommandServerInterface
{
    public function __construct(
        private readonly RpcCommandExecutorInterface $commandExecutor,
        private readonly RpcResultSenderInterface $resultSender,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws TimeoutException
     * @throws CommandNotFoundException
     * @throws InvalidParametersException
     */
    public function handle(RpcCommand $command): void
    {
        $this->logger->debug('RpcServer: command received', [
            'commandId' => $command->getCommandId(),
            'command' => $command->getCommand(),
            'arguments' => $command->getArguments(),
            'timeOutAt' => $command->getTimeoutAt(),
        ]);

        $currentTimestamp = $this->clock->now()->getTimestamp();
        if ($command->getTimeoutAt() < $currentTimestamp) {
            $this->logger->debug('RpcServer: the command has timed out before executing the command');

            throw new TimeoutException();
        }

        try {
            $result = $this->commandExecutor->execute($command->getCommandId(), $command->getCommand(), $command->getArguments());

            $currentTimestamp = $this->clock->now()->getTimestamp();
            $result = new RpcResult($currentTimestamp, $command->getCommandId(), RpcResultStatus::SUCCESS, $result);
            $this->logger->debug('RpcServer: command executed', [
                'executedAt' => $result->getExecutedAt(),
                'commandId' => $result->getCommandId(),
                'status' => $result->getStatus(),
                'result' => $result->getResult(),
            ]);

            if ($command->getTimeoutAt() < $currentTimestamp) {
                $this->logger->debug('RpcServer: the command has timed out before sending the result');

                throw new TimeoutException();
            }

            $this->resultSender->send($command, $result);
        } catch (ClientException $exception) {
            $this->logger->error('RpcCommandExecutor: Client exception', [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);

            $currentTimestamp = $this->clock->now()->getTimestamp();
            $result = new RpcResult($currentTimestamp, $command->getCommandId(), RpcResultStatus::ERROR, [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);

            $this->resultSender->send($command, $result);
        }

        $this->logger->debug('RpcServer: result sent');
    }
}
