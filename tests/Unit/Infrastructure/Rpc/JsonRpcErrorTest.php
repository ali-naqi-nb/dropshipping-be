<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc;

use App\Infrastructure\Rpc\Exception\InvalidRequestException;
use App\Infrastructure\Rpc\JsonRpcError;
use App\Tests\Unit\UnitTestCase;

final class JsonRpcErrorTest extends UnitTestCase
{
    public function testFromRpcException(): void
    {
        $exception = new InvalidRequestException();
        $error = JsonRpcError::fromRpcException($exception);

        $this->assertSame($exception->getCode(), $error->getCode());
        $this->assertSame($exception->getMessage(), $error->getMessage());
    }
}
