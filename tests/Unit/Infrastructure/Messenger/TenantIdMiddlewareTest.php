<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Messenger;

use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Domain\Model\Tenant\TenantService;
use App\Infrastructure\Messenger\TenantIdMiddleware;
use App\Infrastructure\Messenger\TenantIdStamp;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Persistence\Connection\RedisTenantConnection;
use App\Tests\Shared\Factory\DbConfigFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class TenantIdMiddlewareTest extends UnitTestCase
{
    public function testMiddlewareAddTenantIdStamp(): void
    {
        $envelope = new Envelope(new stdClass());

        $doctrineTenantConnection = $this->createMock(DoctrineTenantConnection::class);
        $doctrineTenantConnection->method('getTenantId')
            ->willReturn(TenantFactory::TENANT_ID);

        $redisTenantConnectionMock = $this->createMock(RedisTenantConnection::class);
        $tenantStorageMock = $this->createMock(TenantStorageInterface::class);

        $serviceMock = $this->createMock(TenantService::class);
        $tenantIdMiddleware = new TenantIdMiddleware($doctrineTenantConnection, $redisTenantConnectionMock, $tenantStorageMock, $serviceMock);

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $stack->method('next')
            ->willReturn($nextMiddleware);

        $nextMiddleware->method('handle')
            ->with(
                $this->callback(function (Envelope $envelope): bool {
                    /** @var TenantIdStamp $tenantIdStamp */
                    $tenantIdStamp = $envelope->last(TenantIdStamp::class);
                    $this->assertInstanceOf(TenantIdStamp::class, $tenantIdStamp);
                    $this->assertSame(TenantFactory::TENANT_ID, $tenantIdStamp->getId());

                    return true;
                })
            );

        $tenantIdMiddleware->handle($envelope, $stack);
    }

    public function testMiddlewareReadTenantIdStamp(): void
    {
        $envelope = new Envelope(new stdClass());
        $envelope = $envelope->with(new TenantIdStamp(TenantFactory::TENANT_ID));

        $dbConfig = DbConfigFactory::getDbConfig();

        $redisTenantConnectionMock = $this->createMock(RedisTenantConnection::class);
        $redisTenantConnectionMock->expects($this->once())
            ->method('connect');

        $doctrineTenantConnection = $this->createMock(DoctrineTenantConnection::class);
        $doctrineTenantConnection->expects($this->once())
            ->method('getTenantId')
            ->willReturn(null);
        $doctrineTenantConnection->expects($this->once())
            ->method('create')
            ->with($dbConfig);

        $tenantStorageMock = $this->createMock(TenantStorageInterface::class);
        $tenantStorageMock->expects($this->once())
            ->method('setId')
            ->with(TenantFactory::TENANT_ID);

        $serviceMock = $this->createMock(TenantService::class);
        $serviceMock->expects($this->once())
            ->method('getDbConfig')
            ->with(TenantFactory::TENANT_ID)
            ->willReturn($dbConfig);
        $tenantIdMiddleware = new TenantIdMiddleware($doctrineTenantConnection, $redisTenantConnectionMock, $tenantStorageMock, $serviceMock);

        $stackMock = $this->createMock(StackInterface::class);
        $nextMiddlewareMock = $this->createMock(MiddlewareInterface::class);

        $stackMock->method('next')
            ->willReturn($nextMiddlewareMock);

        $nextMiddlewareMock->method('handle')
            ->with($envelope, $stackMock);

        $tenantIdMiddleware->handle($envelope, $stackMock);
    }
}
