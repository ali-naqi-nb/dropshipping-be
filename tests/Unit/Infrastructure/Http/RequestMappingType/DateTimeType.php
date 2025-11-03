<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

use DateTimeImmutable;

final class DateTimeType
{
    public function __construct(private DateTimeImmutable $dateTime)
    {
    }

    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }
}
