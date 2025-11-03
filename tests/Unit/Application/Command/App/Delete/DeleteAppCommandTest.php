<?php

namespace App\Tests\Unit\Application\Command\App\Delete;

use App\Application\Command\App\Delete\DeleteAppCommand;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class DeleteAppCommandTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $command = new DeleteAppCommand(
            TenantFactory::TENANT_ID,
            AppFactory::ALI_EXPRESS_ID,
        );
        $this->assertSame(TenantFactory::TENANT_ID, $command->getTenantId());
        $this->assertSame(AppFactory::ALI_EXPRESS_ID, $command->getAppId());
    }
}
