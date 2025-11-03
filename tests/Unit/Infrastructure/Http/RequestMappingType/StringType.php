<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class StringType
{
    public function __construct(private string $string)
    {
    }

    public function getString(): string
    {
        return $this->string;
    }
}
