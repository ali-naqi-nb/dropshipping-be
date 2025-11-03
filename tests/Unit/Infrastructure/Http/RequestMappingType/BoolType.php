<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class BoolType
{
    public function __construct(private bool $bool)
    {
    }

    public function getBool(): bool
    {
        return $this->bool;
    }
}
