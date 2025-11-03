<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Transport\Cache;

use App\Infrastructure\Rpc\Transport\Cache\CacheKeyGenerator;
use App\Tests\Unit\UnitTestCase;

final class CacheKeyGeneratorTest extends UnitTestCase
{
    public function testGet(): void
    {
        $generator = new CacheKeyGenerator();

        $this->assertEquals('rpc_123', $generator->get('123'));
    }
}
