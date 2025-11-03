<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Infrastructure\Rpc\RpcCommand;

final class RpcCommandFactory
{
    public const SENT_AT = 1704207845; // 2024-01-02 03:04:05
    public const TIMEOUT_AT = 1704207855; // 2024-01-02 03:04:15
    public const COMMAND_ID = 'command-id';
    public const COMMAND = 'command';
    public const ARGUMENTS = ['argument1', 'argument2'];
    public const GET_ORDERS_BY_SOURCE = 'getOrdersBySource';

    public static function getRpcCommand(
        int $sentAt = self::SENT_AT,
        int $timeoutAt = self::TIMEOUT_AT,
        string $commandId = self::COMMAND_ID,
        string $command = self::COMMAND,
        array $arguments = self::ARGUMENTS,
        ?string $tenantId = null
    ): RpcCommand {
        return new RpcCommand(
            $sentAt,
            $timeoutAt,
            $commandId,
            $command,
            $arguments,
            $tenantId
        );
    }
}
