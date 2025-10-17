<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class FloatType
{
    public function __construct(private float $float)
    {
    }

    public function getFloat(): float
    {
        return $this->float;
    }
}
