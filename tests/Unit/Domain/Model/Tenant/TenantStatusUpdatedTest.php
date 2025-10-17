<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\TenantStatusUpdated;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class TenantStatusUpdatedTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $tenantProductStatusUpdated = new TenantStatusUpdated(
            TenantFactory::TENANT_ID,
            TenantFactory::TENANT_STATUS_TEST->value
        );
        $this->assertSame(TenantFactory::TENANT_ID, $tenantProductStatusUpdated->getTenantId());
        $this->assertSame(TenantFactory::TENANT_STATUS_TEST, $tenantProductStatusUpdated->getStatus());
    }
}
