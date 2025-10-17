<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Tests\Shared\Factory\ServiceDbConfiguredFactory;
use App\Tests\Unit\UnitTestCase;

final class ServiceDbConfiguredTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $event = ServiceDbConfiguredFactory::getServiceDbConfigured();

        $this->assertSame(ServiceDbConfiguredFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame(ServiceDbConfiguredFactory::SERVICE_NAME, $event->getServiceName());
    }
}
