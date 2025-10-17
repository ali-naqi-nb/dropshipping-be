<?php

declare(strict_types=1);

namespace App\Domain\Model\Error;

final class ConstraintViolation
{
    public const PATH_COMMON = 'common';

    public const PATH_MESSAGE = 'message';

    public function __construct(private readonly string $message, private readonly string $path = self::PATH_COMMON)
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
