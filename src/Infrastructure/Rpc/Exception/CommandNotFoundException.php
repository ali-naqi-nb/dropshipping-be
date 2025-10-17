<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Exception;

use RuntimeException;

final class CommandNotFoundException extends RuntimeException implements ClientException
{
    /**
     * @see https://www.jsonrpc.org/specification
     */
    public const CODE = -32601;

    public function __construct(string $message = 'Command not found')
    {
        parent::__construct($message, self::CODE);
    }
}
