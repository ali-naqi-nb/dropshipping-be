<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class NullableIntType
{
    public function __construct(private ?int $nullableInt)
    {
    }

    public function getNullableInt(): ?int
    {
        return $this->nullableInt;
    }
}
