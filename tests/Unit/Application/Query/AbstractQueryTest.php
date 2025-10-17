<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query;

use App\Tests\Unit\UnitTestCase;

final class AbstractQueryTest extends UnitTestCase
{
    public function testToArray(): void
    {
        $command = new DummyQuery('test');
        $this->assertSame([
            'test' => 'test',
            'isActive' => true,
            'gettingValue' => 'asd',
            'nullableValue' => null,
        ], $command->toArray());
    }
}
