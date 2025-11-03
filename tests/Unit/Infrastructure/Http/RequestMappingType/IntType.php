<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class IntType
{
    public function __construct(private int $int)
    {
    }

    public function getInt(): int
    {
        return $this->int;
    }
}
