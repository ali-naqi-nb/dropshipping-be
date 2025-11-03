<?php

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\TenantConfigUpdated;
use App\Tests\Shared\Factory\TenantConfigUpdatedFactory;
use App\Tests\Unit\UnitTestCase;

class TenantConfigUpdatedTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $event = new TenantConfigUpdated(
            TenantConfigUpdatedFactory::TENANT_ID,
            TenantConfigUpdatedFactory::DEFAULT_LANGUAGE,
            TenantConfigUpdatedFactory::DEFAULT_CURRENCY
        );

        $this->assertSame(TenantConfigUpdatedFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame(TenantConfigUpdatedFactory::DEFAULT_LANGUAGE, $event->getDefaultLanguage());
        $this->assertSame(TenantConfigUpdatedFactory::DEFAULT_CURRENCY, $event->getDefaultCurrency());
    }
}
