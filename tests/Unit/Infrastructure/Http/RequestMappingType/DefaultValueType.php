<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class DefaultValueType
{
    public const DEFAULT_VALUE = 123;
    public const NON_DEFAULT_VALUE = 567;

    public function __construct(private readonly int $default = self::DEFAULT_VALUE)
    {
    }

    public function getDefault(): int
    {
        return $this->default;
    }
}
