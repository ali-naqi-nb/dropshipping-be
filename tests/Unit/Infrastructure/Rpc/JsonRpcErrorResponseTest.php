<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc;

use App\Infrastructure\Rpc\Exception\InvalidRequestException;
use App\Infrastructure\Rpc\JsonRpcErrorResponse;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Uid\Uuid;

final class JsonRpcErrorResponseTest extends UnitTestCase
{
    public function testFromRpcException(): void
    {
        $exception = new InvalidRequestException();
        $id = Uuid::v4()->__toString();
        $response = JsonRpcErrorResponse::fromRpcException($id, $exception);

        $this->assertSame($id, $response->getId());
        $this->assertSame('2.0', $response->getJsonrpc());
        $this->assertSame($exception->getCode(), $response->getError()->getCode());
        $this->assertSame($exception->getMessage(), $response->getError()->getMessage());
    }
}
