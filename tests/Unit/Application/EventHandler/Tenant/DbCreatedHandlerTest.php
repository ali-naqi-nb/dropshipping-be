<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\EventHandler\Tenant;

use App\Application\EventHandler\Tenant\DbCreatedHandler;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Tests\Shared\Factory\DbCreatedFactory;
use App\Tests\Unit\UnitTestCase;

final class DbCreatedHandlerTest extends UnitTestCase
{
    public function testInvokeCallsTenantServiceCreate(): void
    {
        $dbCreated = DbCreatedFactory::getDbCreated();

        $tenantServiceMock = $this->createMock(TenantServiceInterface::class);
        $tenantServiceMock->expects($this->once())
            ->method('create')
            ->with($dbCreated);

        $handler = new DbCreatedHandler($tenantServiceMock);
        $handler($dbCreated);
    }
}
