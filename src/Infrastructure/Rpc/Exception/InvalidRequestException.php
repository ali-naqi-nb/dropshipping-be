<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Exception;

use App\Application\Shared\Error\ErrorResponse;
use RuntimeException;

final class InvalidRequestException extends RuntimeException implements ClientException
{
    /**
     * @see https://www.jsonrpc.org/specification
     */
    public const CODE = -32600;

    public function __construct(
        string $message = 'Invalid request',
        private readonly mixed $data = null,
    ) {
        parent::__construct($message, self::CODE);
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public static function fromErrorResponse(ErrorResponse $errorResponse): InvalidRequestException
    {
        return new self(data: $errorResponse->getErrors());
    }
}
