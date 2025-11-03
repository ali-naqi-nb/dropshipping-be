<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Service;

use DateTimeInterface;

interface ClockInterface
{
    public function now(): DateTimeInterface;
}
