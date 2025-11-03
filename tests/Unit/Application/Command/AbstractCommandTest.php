<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Tests\Unit\UnitTestCase;

final class AbstractCommandTest extends UnitTestCase
{
    public function testToArray(): void
    {
        $command = new DummyCommand('test');
        $this->assertSame([
            'test' => 'test',
            'isActive' => true,
            'gettingValue' => 'asd',
            'nullableValue' => null,
        ], $command->toArray());
    }
}
