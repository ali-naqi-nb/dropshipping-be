<?php

declare(strict_types=1);

namespace App\Tests\Double\Rpc\Transport;

use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\Transport\RpcResultSenderInterface;

final class MockResultSender implements RpcResultSenderInterface
{
    private array $sent = [];

    public function send(RpcCommand $command, RpcResult $result): void
    {
        $this->sent[$command->getCommandId()] = [$command, $result];
    }

    public function getSent(RpcCommand $call): ?RpcResult
    {
        return $this->sent[$call->getCommandId()][1] ?? null;
    }

    public function getSentByCallId(string $callId): ?RpcResult
    {
        return $this->sent[$callId][1] ?? null;
    }

    public function clear(): void
    {
        $this->sent = [];
    }
}
