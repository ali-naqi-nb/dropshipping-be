<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Collector;

use App\Infrastructure\Collector\MultiDbCollector;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Shared\Factory\DbConfigFactory;
use App\Tests\Unit\UnitTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MultiDbCollectorTest extends UnitTestCase
{
    public function testGetName(): void
    {
        $defaultConnection = $this->createMock(Connection::class);
        $tenantConnection = $this->createMock(DoctrineTenantConnection::class);

        $collector = new MultiDbCollector($defaultConnection, $tenantConnection);
        $this->assertSame('database_connections', $collector->getName());
    }

    public function testCollect(): void
    {
        $defaultConnection = $this->createMock(Connection::class);
        $defaultConnection->expects($this->once())
            ->method('getDatabase')
            ->willReturn('service');
        $tenantConnection = $this->createMock(DoctrineTenantConnection::class);
        $tenantConnection->expects($this->once())
            ->method('isConnected')
            ->willReturn(true);
        $tenantConnection->expects($this->once())
            ->method('getDatabase')
            ->willReturn(DbConfigFactory::getDatabase());
        $collector = new MultiDbCollector($defaultConnection, $tenantConnection);
        $requestMock = $this->createMock(Request::class);
        $responseMock = $this->createMock(Response::class);
        $exceptionMock = $this->createMock(Throwable::class);
        $collector->collect($requestMock, $responseMock, $exceptionMock);

        $this->assertSame('service', $collector->getDefault());
        $this->assertSame(DbConfigFactory::getDatabase(), $collector->getTenant());
    }

    /*
         * public function __construct(private Connection $defaultConnection, private Connection $tenantConnection)
        {
        }

        public function getDefault(): string
        {
            return $this->data['default'];
        }

        public function getTenant(): string
        {
            return $this->data['tenant'];
        }

        public function collect(Request $request, Response $response, Throwable $exception = null): void
        {
            $this->data['default'] = $this->defaultConnection->getDatabase();
            $this->data['tenant'] = $this->tenantConnection->isConnected() ? $this->tenantConnection->getDatabase() : null;
        }

        public function getName(): string
        {
            return 'database_connections';
        }
         */
}
