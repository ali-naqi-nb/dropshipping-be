<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Service;

use DateTimeImmutable;
use DateTimeInterface;

final class Clock implements ClockInterface
{
    public function now(): DateTimeInterface
    {
        return new DateTimeImmutable();
    }
}
