<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Client;

use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Rpc\Client\RpcCommandClient;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Infrastructure\Rpc\Service\CallIdGeneratorInterface;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Infrastructure\Rpc\Transport\RpcCommandSenderInterface;
use App\Infrastructure\Rpc\Transport\RpcResultReceiverInterface;
use App\Tests\Unit\UnitTestCase;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

final class RpcCommandClientTest extends UnitTestCase
{
    private RpcCommandSenderInterface&MockObject $commandSender;
    private RpcResultReceiverInterface&MockObject $resultReceiver;
    private CallIdGeneratorInterface&MockObject $idGenerator;
    private ClockInterface&MockObject $clock;
    private LoggerInterface&MockObject $logger;
    private RpcCommandClient $commandClient;
    private TenantStorageInterface $tenantStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandSender = $this->createMock(RpcCommandSenderInterface::class);
        $this->resultReceiver = $this->createMock(RpcResultReceiverInterface::class);
        $this->idGenerator = $this->createMock(CallIdGeneratorInterface::class);
        $this->tenantStorage = $this->createMock(TenantStorageInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->commandClient = new RpcCommandClient(
            $this->commandSender,
            $this->resultReceiver,
            $this->idGenerator,
            $this->tenantStorage,
            $this->clock,
            $this->logger,
        );
    }

    public function testCallSendsCallAndReceivesResult(): void
    {
        $timestamp = 1234567890;
        $service = 'test_service';
        $command = 'test_command';
        $arguments = ['arg1' => 'value1', 'arg2' => 'value2'];
        $timeout = 10;
        $commandId = 'test_rpc_id';
        $logs = [];

        $this->idGenerator->method('generate')->willReturn($commandId);
        $this->clock->method('now')->willReturn(new DateTimeImmutable('@'.$timestamp));
        $this->logger->method('debug')
            ->willReturnCallback(function () use (&$logs) {
                $logs[] = func_get_args();
            });

        $rpcCommand = new RpcCommand(
            sentAt: $timestamp,
            timeoutAt: $timestamp + $timeout,
            commandId: $commandId,
            command: 'test_service.test_command',
            arguments: ['arg1' => 'value1', 'arg2' => 'value2'],
        );

        $result = new RpcResult($timestamp + 15, $commandId, RpcResultStatus::SUCCESS, 'data');

        $this->commandSender->expects($this->once())
            ->method('send')
            ->with($rpcCommand);

        $this->resultReceiver->expects($this->once())
            ->method('receive')
            ->with($rpcCommand)
            ->willReturn($result);

        $expectedLogs = [
            ['RPCClient: sending a command', [
                'commandId' => $commandId,
                'command' => 'test_service.test_command',
                'arguments' => ['arg1' => 'value1', 'arg2' => 'value2'],
                'sentAt' => $timestamp,
                'timeoutAt' => $timestamp + 10,
                'tenantId' => null,
            ]],
            ['RPCClient: the command has been sent', []],
            ['RPCClient: received a result', [
                'status' => RpcResultStatus::SUCCESS,
                'executedAt' => $timestamp + 15,
                'result' => 'data',
            ]],
        ];

        $this->assertSame($result, $this->commandClient->call($service, $command, $arguments, $timeout));
        $this->assertSame($expectedLogs, $logs);
    }

    /**
     * @dataProvider provideInvalidTimeouts
     */
    public function testTimeoutMustBeGreaterThanZero(int $timeout): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be greater than 0');

        $this->commandClient->call('test_service', 'test_command', [], $timeout);
    }

    public function provideInvalidTimeouts(): array
    {
        return [
            'zero' => [
                'timeout' => 0,
            ],
            'negative' => [
                'timeout' => -1,
            ],
        ];
    }
}
