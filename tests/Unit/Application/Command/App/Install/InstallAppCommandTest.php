<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\App\Install;

use App\Application\Command\App\Install\InstallAppCommand;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class InstallAppCommandTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $command = new InstallAppCommand(TenantFactory::TENANT_ID, AppFactory::ALI_EXPRESS_ID);

        $this->assertSame(TenantFactory::TENANT_ID, $command->getTenantId());
        $this->assertSame(AppFactory::ALI_EXPRESS_ID, $command->getAppId());
    }
}
