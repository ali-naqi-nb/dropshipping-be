<?php

declare(strict_types=1);

namespace App\Tests\Double\Rpc\Transport;

use App\Infrastructure\Rpc\Exception\CommandNotFoundException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\Transport\ExceptionRpcResultReceiver\ExceptionFactoryInterface;
use App\Infrastructure\Rpc\Transport\RpcResultReceiverInterface;
use Closure;
use Throwable;

final class MockResultReceiver implements RpcResultReceiverInterface
{
    public function __construct(
        private readonly ExceptionFactoryInterface $exceptionFactory,
        private array $mocks = [],
    ) {
    }

    /**
     * @throws Throwable
     */
    public function receive(RpcCommand $command): RpcResult
    {
        foreach ($this->mocks as $mockedCalls) {
            /** @var Closure $matchCallback */
            $matchCallback = $mockedCalls[0];

            if ($matchCallback($command)) {
                /** @var RpcResult $result */
                $result = $mockedCalls[1];

                $exception = $this->exceptionFactory->fromResult($result);

                if (null !== $exception) {
                    throw $exception;
                }

                return $result;
            }
        }

        throw new CommandNotFoundException();
    }

    public function mock(Closure $matchCallback, RpcResult $result): void
    {
        $this->mocks[] = [$matchCallback, $result];
    }

    public function clear(): void
    {
        $this->mocks = [];
    }
}
