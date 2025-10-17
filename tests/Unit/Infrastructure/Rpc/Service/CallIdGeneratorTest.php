<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Service;

use App\Infrastructure\Rpc\Service\CallIdGenerator;
use App\Tests\Unit\UnitTestCase;

final class CallIdGeneratorTest extends UnitTestCase
{
    public function testGenerate(): void
    {
        $generator = new CallIdGenerator();
        $id1 = $generator->generate();
        $id2 = $generator->generate();

        $this->assertStringStartsWith('rpc_', $id1);
        $this->assertStringStartsWith('rpc_', $id2);
        $this->assertNotSame($id1, $id2);
    }
}
