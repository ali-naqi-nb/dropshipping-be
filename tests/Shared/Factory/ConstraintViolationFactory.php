<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Error\ConstraintViolation;

final class ConstraintViolationFactory
{
    public const PATH_COMMON = 'common';
    public const PATH = 'translation.en_US.slug';
    public const MESSAGE = 'test message';

    public static function getConstraintViolation(
        string $path = self::PATH,
        string $message = self::MESSAGE,
    ): ConstraintViolation {
        return new ConstraintViolation($path, $message);
    }
}
