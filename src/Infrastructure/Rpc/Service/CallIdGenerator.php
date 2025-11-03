<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Service;

final class CallIdGenerator implements CallIdGeneratorInterface
{
    private const BYTE_LENGTH = 22;

    /**
     * @throws \Exception
     */
    public function generate(): string
    {
        return 'rpc_'.bin2hex(random_bytes(self::BYTE_LENGTH));
    }
}
