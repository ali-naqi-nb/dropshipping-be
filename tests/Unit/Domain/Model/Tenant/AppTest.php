<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Unit\UnitTestCase;

final class AppTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $appId = AppId::AliExpress;
        $app = new App($appId, AppFactory::INSTALLED_AND_ACTIVATED_CONFIG);
        $this->assertSame($appId, $app->getAppId());
        $this->assertTrue($app->isInstalled());
        $this->assertTrue($app->isActive());
        $this->assertMatchesPattern(AppFactory::INSTALLED_AND_ACTIVATED_CONFIG, $app->getConfig());
    }

    public function testSetters(): void
    {
        $app = AppFactory::getApp();
        $newConfig = ['isActive' => false, 'isInstalled' => false];
        $this->assertTrue($app->isActive());
        $this->assertTrue($app->isInstalled());
        $this->assertNotSame($app->getConfig(), $newConfig);

        $app->setConfig($newConfig);
        $this->assertSame($app->getConfig(), $newConfig);
        $this->assertFalse($app->isActive());
        $this->assertFalse($app->isInstalled());

        // test appendConfig
        $this->assertArrayNotHasKey('testKey', $app->getConfig());
        $app->appendConfig('testKey', 'testValue');
        $this->assertArrayHasKey('testKey', $app->getConfig());
        $this->assertEquals('testValue', $app->getConfig()['testKey']);
    }

    public function testFromAppData(): void
    {
        $appId = AppId::AliExpress;
        $appData = AppFactory::INSTALLED_AND_ACTIVATED_CONFIG;
        $app = App::fromAppData($appId, $appData);
        $this->assertSame($appId, $app->getAppId());
        $this->assertSame($appData['isInstalled'], $app->isInstalled());
        $this->assertSame($appData['isActive'], $app->isActive());
        $this->assertMatchesPattern($appData, $app->getConfig());
    }

    public function testCreateWithDefaults(): void
    {
        $app = App::createWithDefaults(AppId::from(AppFactory::ALI_EXPRESS_ID));
        $this->assertSame(AppFactory::ALI_EXPRESS_ID, $app->getAppId()->value);
        $this->assertFalse($app->isInstalled());
        $this->assertFalse($app->isActive());
        $this->assertMatchesPattern(AppFactory::APP_DEFAULT_CONFIG, $app->getConfig());
    }

    public function testInstall(): void
    {
        $app = AppFactory::getApp(config: AppFactory::APP_DEFAULT_CONFIG);
        $this->assertFalse($app->isInstalled());

        $app->install();
        $this->assertTrue($app->isInstalled());
    }
}
