<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Exception;

use RuntimeException;
use Throwable;

final class InternalServerErrorException extends RuntimeException implements ServerException
{
    /**
     * @see https://www.jsonrpc.org/specification
     */
    public const CODE = -32000;

    public function __construct(
        string $message = 'Internal server error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, self::CODE, $previous);
    }
}
