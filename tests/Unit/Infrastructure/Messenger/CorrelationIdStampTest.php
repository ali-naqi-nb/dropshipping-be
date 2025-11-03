<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Messenger;

use App\Infrastructure\Messenger\CorrelationIdStamp;
use App\Tests\Shared\Factory\CorrelationIdFactory;
use App\Tests\Unit\UnitTestCase;

final class CorrelationIdStampTest extends UnitTestCase
{
    public function testSetCorrelationIdCanBeSet(): void
    {
        $correlationIdStamp = new CorrelationIdStamp(CorrelationIdFactory::CORRELATION_ID);

        $this->assertSame(CorrelationIdFactory::CORRELATION_ID, $correlationIdStamp->getId());
    }
}
