<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\Cache;

final class CacheKeyGenerator implements CacheKeyGeneratorInterface
{
    public function get(string $commandId): string
    {
        return "rpc_$commandId";
    }
}
