<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\App\Authorize;

use App\Application\Command\App\Authorize\AuthorizeAppCommand;
use App\Domain\Model\Tenant\AppId;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Random\Generator;
use App\Tests\Unit\UnitTestCase;

final class AuthorizeAppCommandTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $token = Generator::string();
        $command = new AuthorizeAppCommand(
            tenantId: TenantFactory::TENANT_ID,
            appId: AppId::AliExpress->value,
            token: $token
        );

        $this->assertSame(TenantFactory::TENANT_ID, $command->getTenantId());
        $this->assertSame(AppId::AliExpress->value, $command->getAppId());
        $this->assertSame($token, $command->getToken());
    }
}
