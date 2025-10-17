<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\DropshippingDbCreated;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class DropshippingDbCreatedTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $event = new DropshippingDbCreated(
            tenantId: TenantFactory::TENANT_ID,
            defaultCurrency: TenantFactory::DEFAULT_CURRENCY,
            defaultLanguage: TenantFactory::DEFAULT_LANGUAGE,
            companyId: TenantFactory::COMPANY_ID,
            domain: TenantFactory::DOMAIN,
            config: TenantFactory::getConfig(),
            status: TenantFactory::TENANT_STATUS_TEST->value
        );

        $this->assertSame(TenantFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame(TenantFactory::COMPANY_ID, $event->getCompanyId());
        $this->assertSame(TenantFactory::DOMAIN, $event->getDomain());
        $this->assertSame(TenantFactory::getConfig(), $event->getConfig());
        $this->assertSame(TenantFactory::DEFAULT_LANGUAGE, $event->getDefaultLanguage());
        $this->assertSame(TenantFactory::DEFAULT_CURRENCY, $event->getDefaultCurrency());
        $this->assertSame(TenantFactory::getConfig(), $event->getConfig());
        $this->assertSame(TenantFactory::TENANT_STATUS_TEST, $event->getStatus());
        $this->assertFalse($event->isDbCreated());
    }

    public function testGettersWithAllParametersReturnCorrectData(): void
    {
        $event = new DropshippingDbCreated(
            tenantId: TenantFactory::TENANT_ID,
            defaultCurrency: TenantFactory::DEFAULT_CURRENCY,
            defaultLanguage: TenantFactory::DEFAULT_LANGUAGE,
            companyId: TenantFactory::COMPANY_ID,
            domain: TenantFactory::DOMAIN,
            config: TenantFactory::getConfig(),
            status: TenantFactory::TENANT_STATUS_TEST->value,
            dbCreated: true
        );

        $this->assertSame(TenantFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame(TenantFactory::COMPANY_ID, $event->getCompanyId());
        $this->assertSame(TenantFactory::DOMAIN, $event->getDomain());
        $this->assertSame(TenantFactory::getConfig(), $event->getConfig());
        $this->assertSame(TenantFactory::DEFAULT_LANGUAGE, $event->getDefaultLanguage());
        $this->assertSame(TenantFactory::DEFAULT_CURRENCY, $event->getDefaultCurrency());
        $this->assertSame(TenantFactory::getConfig(), $event->getConfig());
        $this->assertSame(TenantFactory::TENANT_STATUS_TEST, $event->getStatus());
        $this->assertTrue($event->isDbCreated());
    }
}
