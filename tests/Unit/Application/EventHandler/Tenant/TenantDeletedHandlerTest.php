<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\EventHandler\Tenant;

use App\Application\EventHandler\Tenant\TenantDeletedHandler;
use App\Domain\Model\Tenant\TenantDeleted;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class TenantDeletedHandlerTest extends UnitTestCase
{
    public function testOk(): void
    {
        $tenantServiceMock = $this->createMock(TenantServiceInterface::class);
        $event = new TenantDeleted(TenantFactory::TENANT_ID);
        $tenantServiceMock->expects($this->once())
            ->method('removeTenant');

        $handler = new TenantDeletedHandler($tenantServiceMock);
        $handler->__invoke($event);
    }
}
