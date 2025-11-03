<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class DefaultStringType
{
    public const DEFAULT_VALUE = 'test';

    public function __construct(private readonly string $default = self::DEFAULT_VALUE)
    {
    }

    public function getDefault(): string
    {
        return $this->default;
    }
}
