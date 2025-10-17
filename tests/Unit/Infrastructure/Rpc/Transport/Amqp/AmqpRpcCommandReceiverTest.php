<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Transport\Amqp;

use App\Domain\Model\Tenant\DbConfig;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Persistence\Connection\RedisTenantConnection;
use App\Infrastructure\Rpc\Exception\TimeoutException;
use App\Infrastructure\Rpc\Server\RpcCommandServerInterface;
use App\Infrastructure\Rpc\Transport\Amqp\AmqpRpcCommandReceiver;
use App\Tests\Shared\Factory\RpcCommandFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class AmqpRpcCommandReceiverTest extends UnitTestCase
{
    private RpcCommandServerInterface&MockObject $commandServer;
    private AmqpRpcCommandReceiver $commandReceiver;
    private TenantStorageInterface&MockObject $tenantStorage;
    private DoctrineTenantConnection&MockObject $doctrineTenantConnection;
    private TenantServiceInterface&MockObject $service;
    private RedisTenantConnection&MockObject $redisTenantConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandServer = $this->createMock(RpcCommandServerInterface::class);
        $this->tenantStorage = $this->createMock(TenantStorageInterface::class);
        $this->doctrineTenantConnection = $this->createMock(DoctrineTenantConnection::class);
        $this->service = $this->createMock(TenantServiceInterface::class);
        $this->redisTenantConnection = $this->createMock(RedisTenantConnection::class);
        $this->commandReceiver = new AmqpRpcCommandReceiver(
            $this->commandServer,
            $this->doctrineTenantConnection,
            $this->tenantStorage,
            $this->service,
            $this->redisTenantConnection
        );
    }

    public function testInvoke(): void
    {
        $rpcCommand = RpcCommandFactory::getRpcCommand();

        $this->commandServer->expects($this->once())
            ->method('handle')
            ->with($rpcCommand);

        $this->commandReceiver->__invoke($rpcCommand);
    }

    public function testInvokeWithTenantIdInCommand(): void
    {
        $rpcCommand = RpcCommandFactory::getRpcCommand(tenantId: TenantFactory::TENANT_ID);
        $dbConfig = $this->createMock(DbConfig::class);

        $this->commandServer->expects($this->once())
            ->method('handle')
            ->with($rpcCommand);

        $this->service->expects($this->once())
            ->method('getDbConfig')
            ->with(TenantFactory::TENANT_ID)
            ->willReturn($dbConfig);

        $this->tenantStorage->expects($this->once())
            ->method('setId')
            ->with(TenantFactory::TENANT_ID);

        $this->doctrineTenantConnection->expects($this->once())
            ->method('create')
            ->with($dbConfig);

        $this->redisTenantConnection->expects($this->once())
            ->method('connect');

        $this->commandReceiver->__invoke($rpcCommand);
    }

    public function testInvokeTimeout(): void
    {
        $rpcCommand = RpcCommandFactory::getRpcCommand();

        $this->commandServer->expects($this->once())
            ->method('handle')
            ->willThrowException(new TimeoutException());

        $this->commandReceiver->__invoke($rpcCommand);
    }
}
