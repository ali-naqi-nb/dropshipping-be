<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

use DateTimeImmutable;

final class NullableDateTimeType
{
    public function __construct(private ?DateTimeImmutable $nullableDateTime)
    {
    }

    public function getNullableDateTime(): ?DateTimeImmutable
    {
        return $this->nullableDateTime;
    }
}
