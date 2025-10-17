<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class NullableFloatType
{
    public function __construct(private ?FloatType $nullableFloatCommand)
    {
    }

    public function getNullableFloat(): ?float
    {
        return $this->nullableFloatCommand?->getFloat();
    }
}
