<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class NullableStringType
{
    public function __construct(private ?string $string)
    {
    }

    public function getNullableString(): ?string
    {
        return $this->string;
    }
}
