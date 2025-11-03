<?php

namespace App\Tests\Unit\Application\EventHandler\Tenant;

use App\Application\EventHandler\Tenant\TenantConfigUpdatedEventHandler;
use App\Domain\Model\Tenant\TenantConfigUpdated;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Tests\Shared\Factory\TenantConfigUpdatedFactory;
use App\Tests\Unit\UnitTestCase;

class TenantConfigUpdatedEventHandlerTest extends UnitTestCase
{
    public function testOk(): void
    {
        $event = new TenantConfigUpdated(
            TenantConfigUpdatedFactory::TENANT_ID,
            TenantConfigUpdatedFactory::DEFAULT_LANGUAGE,
            TenantConfigUpdatedFactory::DEFAULT_CURRENCY
        );
        $tenantServiceInterface = $this->createMock(TenantServiceInterface::class);
        $tenantServiceInterface->expects($this->once())->method('update')->with($event);
        $handler = new TenantConfigUpdatedEventHandler($tenantServiceInterface);
        $handler->__invoke($event);
    }
}
