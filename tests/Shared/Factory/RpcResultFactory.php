<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\RpcResultStatus;

final class RpcResultFactory
{
    public const EXECUTED_AT = 1704207850; // 2024-01-02 03:04:10
    public const COMMAND_ID = 'command-id';
    public const STATUS = RpcResultStatus::SUCCESS;
    public const RESULT = 'result';

    public static function getRpcCommandResult(
        int $executedAt = self::EXECUTED_AT,
        string $commandId = self::COMMAND_ID,
        RpcResultStatus $status = self::STATUS,
        mixed $result = self::RESULT,
    ): RpcResult {
        return new RpcResult($executedAt, $commandId, $status, $result);
    }
}
