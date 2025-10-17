<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Transport\Cache;

interface CacheKeyGeneratorInterface
{
    public function get(string $commandId): string;
}
