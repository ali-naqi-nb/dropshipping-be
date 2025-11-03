<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Exception;

use RuntimeException;

final class InvalidParametersException extends RuntimeException implements ClientException
{
    /**
     * @see https://www.jsonrpc.org/specification
     */
    public const CODE = -32602;

    public function __construct(string $message = 'Invalid parameters')
    {
        parent::__construct($message, self::CODE);
    }
}
