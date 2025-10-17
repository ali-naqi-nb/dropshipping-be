<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Transport\ExceptionRpcResultReceiver;

use App\Infrastructure\Rpc\Exception\CommandNotFoundException;
use App\Infrastructure\Rpc\Exception\InternalServerErrorException;
use App\Infrastructure\Rpc\Exception\InvalidParametersException;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Infrastructure\Rpc\Transport\ExceptionRpcResultReceiver\ExceptionFactory;
use App\Tests\Shared\Factory\RpcResultFactory;
use App\Tests\Unit\UnitTestCase;

final class ExceptionFactoryTest extends UnitTestCase
{
    private ExceptionFactory $exceptionFactory;

    protected function setUp(): void
    {
        $this->exceptionFactory = new ExceptionFactory();
    }

    /**
     * @dataProvider rpcResultDataProvider
     *
     * @param class-string|null $expectedExceptionClass
     */
    public function testFromResult(
        RpcResult $rpcResult,
        ?string $expectedExceptionClass,
        ?string $expectedMessage = null
    ): void {
        $exception = $this->exceptionFactory->fromResult($rpcResult);

        if (null !== $expectedExceptionClass) {
            $this->assertInstanceOf($expectedExceptionClass, $exception);
            $this->assertEquals($expectedMessage, $exception->getMessage());
        } else {
            $this->assertNull($exception);
        }
    }

    public function rpcResultDataProvider(): array
    {
        return [
            'success' => [
                'rpcResult' => RpcResultFactory::getRpcCommandResult(status: RpcResultStatus::SUCCESS),
                'expectedExceptionClass' => null,
            ],
            'CommandNotFoundException' => [
                'rpcResult' => RpcResultFactory::getRpcCommandResult(
                    status: RpcResultStatus::ERROR,
                    result: [
                        'code' => -32601,
                        'message' => 'Not found',
                    ]
                ),
                'expectedExceptionClass' => CommandNotFoundException::class,
                'expectedExceptionMessage' => 'Not found',
            ],
            'InvalidParametersException' => [
                'rpcResult' => RpcResultFactory::getRpcCommandResult(
                    status: RpcResultStatus::ERROR,
                    result: [
                        'code' => -32602,
                        'message' => 'Invalid parameters',
                    ]
                ),
                'expectedExceptionClass' => InvalidParametersException::class,
                'expectedExceptionMessage' => 'Invalid parameters',
            ],
            'InternalServerErrorException' => [
                'rpcResult' => RpcResultFactory::getRpcCommandResult(
                    status: RpcResultStatus::ERROR,
                    result: [
                        'code' => -32000,
                        'message' => 'Internal server error',
                    ]
                ),
                'expectedExceptionClass' => InternalServerErrorException::class,
                'expectedExceptionMessage' => 'Internal server error',
            ],
            'unknownErrorCode' => [
                'rpcResult' => RpcResultFactory::getRpcCommandResult(
                    status: RpcResultStatus::ERROR,
                    result: [
                        'code' => 123,
                        'message' => 'Unknown error',
                    ]
                ),
                'expectedExceptionClass' => null,
            ],
        ];
    }
}
