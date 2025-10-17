<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\App\Update;

use App\Application\Command\App\Update\UpdateAppCommand;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class UpdateAppCommandTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $command = new UpdateAppCommand(
            TenantFactory::TENANT_ID,
            AppFactory::ALI_EXPRESS_ID,
            AppFactory::INSTALLED_AND_ACTIVATED_CONFIG,
        );

        $this->assertSame(TenantFactory::TENANT_ID, $command->getTenantId());
        $this->assertSame(AppFactory::ALI_EXPRESS_ID, $command->getAppId());
        $this->assertSame(AppFactory::INSTALLED_AND_ACTIVATED_CONFIG, $command->getConfig());
    }
}
