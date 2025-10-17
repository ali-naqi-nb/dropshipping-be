<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

final class NullableBoolType
{
    public function __construct(private ?bool $nullableBool)
    {
    }

    public function getNullableBool(): ?bool
    {
        return $this->nullableBool;
    }
}
