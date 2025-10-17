<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class NullableArrayType
{
    public function __construct(private ?array $nullableArray)
    {
    }

    public function getNullableArray(): ?array
    {
        return $this->nullableArray;
    }
}
