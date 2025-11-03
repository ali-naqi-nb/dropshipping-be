<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\App\RefreshToken;

use App\Application\Command\App\RefreshToken\RefreshTokenAppCommand;
use App\Domain\Model\Tenant\AppId;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class RefreshTokenAppCommandTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $command = new RefreshTokenAppCommand(
            tenantId: TenantFactory::TENANT_ID,
            appId: AppId::AliExpress->value
        );

        $this->assertSame(TenantFactory::TENANT_ID, $command->getTenantId());
        $this->assertSame(AppId::AliExpress->value, $command->getAppId());
    }
}
