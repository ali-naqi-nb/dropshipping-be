<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared\App;

use App\Application\Shared\App\AppResponse;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Unit\UnitTestCase;

final class AppResponseTest extends UnitTestCase
{
    public function testFromApp(): void
    {
        $app = AppFactory::getApp();
        $response = AppResponse::fromApp($app);

        $expectedConfig = $app->getConfig();

        $this->assertSame($app->getAppId()->value, $response->getAppId());
        $this->assertSame($expectedConfig, $response->getConfig());
    }
}
