<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Logger;

use App\Infrastructure\Logger\CorrelationIdStorage;
use App\Tests\Shared\Factory\CorrelationIdFactory;
use App\Tests\Unit\UnitTestCase;

final class CorrelationIdStorageTest extends UnitTestCase
{
    public function testGetterReturnEmptyDefault(): void
    {
        $correlationIdStorage = new CorrelationIdStorage();

        $this->assertSame('', $correlationIdStorage->getCorrelationId());
    }

    public function testGetterWithCustomCorrelationIdReturnSame(): void
    {
        $correlationIdStorage = new CorrelationIdStorage();

        $correlationIdStorage->setCorrelationId(CorrelationIdFactory::CORRELATION_ID);

        $this->assertSame(CorrelationIdFactory::CORRELATION_ID, $correlationIdStorage->getCorrelationId());
    }
}
