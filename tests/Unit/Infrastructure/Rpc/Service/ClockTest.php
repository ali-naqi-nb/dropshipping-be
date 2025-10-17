<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Service;

use App\Infrastructure\Rpc\Service\Clock;
use App\Tests\Unit\UnitTestCase;
use DateTimeInterface;

final class ClockTest extends UnitTestCase
{
    public function testNow(): void
    {
        $clock = new Clock();
        $now = $clock->now();

        $this->assertInstanceOf(DateTimeInterface::class, $now);
    }
}
