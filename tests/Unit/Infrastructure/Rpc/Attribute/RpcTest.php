<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Attribute;

use App\Infrastructure\Rpc\Attribute\Rpc;
use App\Tests\Unit\UnitTestCase;

final class RpcTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $rpc = new Rpc('service', 'command');

        $this->assertSame('service', $rpc->getService());
        $this->assertSame('command', $rpc->getCommand());

        $rpc = new Rpc();

        $this->assertNull($rpc->getService());
        $this->assertNull($rpc->getCommand());
    }
}
