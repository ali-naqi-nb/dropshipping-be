<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Error\ConstraintViolation;

final class ErrorResponseFactory
{
    public const MESSAGE_INVALID_UUID = 'This value is not a valid UUID.';
    public const VALID_ID = '0a9654c4-bbc0-4ec7-a2dd-6b3765673364';
    public const INVALID_VALUE_ID = 'invalid-uuid';
    public const MESSAGE_CODE_NOT_UNIQUE = 'Given code is already used.';
    public const MESSAGE_NOT_FOUND = 'Not Found';
    public const PROPERTY_PATH_ID = 'id';
    public const ROOT_ID = [self::PROPERTY_PATH_ID => self::MESSAGE_INVALID_UUID];

    public static function getConstraintViolation(
        string $path = self::PROPERTY_PATH_ID,
        string $message = self::MESSAGE_INVALID_UUID,
    ): ConstraintViolation {
        return new ConstraintViolation(
            path: $path,
            message: $message,
        );
    }
}
