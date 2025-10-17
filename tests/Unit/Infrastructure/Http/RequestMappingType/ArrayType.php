<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class ArrayType
{
    public function __construct(private array $array)
    {
    }

    public function getArray(): array
    {
        return $this->array;
    }
}
