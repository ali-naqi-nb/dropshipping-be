<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Server;

use App\Infrastructure\Rpc\Client\RpcMessageClientInterface;
use App\Infrastructure\Rpc\Exception\CommandNotFoundException;
use App\Infrastructure\Rpc\RpcMessage;
use App\Infrastructure\Rpc\Server\CommandExecutor\RpcCommandExecutorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RpcMessageServer
{
    public function __construct(
        private readonly RpcMessageClientInterface $publisher,
        private readonly RpcCommandExecutorInterface $commandExecutor,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(RpcMessage $message): void
    {
        $this->logger->debug('RpcMessage received', $message->toArray());

        try {
            $result = $this->commandExecutor->execute($message->id, $message->method, $message->arguments);

            if (null !== $message->onSuccess) {
                $this->publisher->reply($message->id, $message->onSuccess, [$result]);
            }
        } catch (CommandNotFoundException $exception) {
            $this->logger->error($exception->getMessage());
        } catch (\Exception $exception) {
            if (null !== $message->onError) {
                // remove closure
                $traceProperty = (new \ReflectionClass($exception))->getProperty('trace');
                $traceProperty->setValue($exception, []);

                $this->publisher->reply($message->id, $message->onError, [$exception]);
            } else {
                $this->logger->error(sprintf('RpcMessage handler error: %s', $exception->getMessage()));
            }
        }
    }
}
