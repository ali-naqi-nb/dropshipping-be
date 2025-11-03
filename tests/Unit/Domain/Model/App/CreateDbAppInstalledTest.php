<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\App;

use App\Domain\Model\App\CreateDbAppInstalled;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class CreateDbAppInstalledTest extends UnitTestCase
{
    public function testGetters(): void
    {
        /** @var string $serviceName */
        $serviceName = getenv('SERVICE_NAME');
        $event = new CreateDbAppInstalled(
            TenantFactory::TENANT_ID, $serviceName, AppFactory::ALI_EXPRESS_ID
        );

        $this->assertSame(TenantFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame($serviceName, $event->getServiceName());
        $this->assertSame(AppFactory::ALI_EXPRESS_ID, $event->getAppId());
    }
}
