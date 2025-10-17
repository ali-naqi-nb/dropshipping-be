<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;
use DateTime;

final class TenantTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $tenant = new Tenant(
            id: TenantFactory::TENANT_ID,
            companyId: TenantFactory::COMPANY_ID,
            domain: TenantFactory::DOMAIN,
            dbConfig: TenantFactory::getConfig(),
            defaultLanguage: TenantFactory::DEFAULT_LANGUAGE,
            defaultCurrency: TenantFactory::DEFAULT_CURRENCY,
            status: TenantFactory::TENANT_STATUS_TEST,
        );

        $datetime = new DateTime();
        $tenant->setDeletedAt($datetime);

        $this->assertSame(TenantFactory::TENANT_ID, $tenant->getId());
        $this->assertSame(TenantFactory::COMPANY_ID, $tenant->getCompanyId());
        $this->assertSame(TenantFactory::DOMAIN, $tenant->getDomain());
        $this->assertSame(TenantFactory::getConfig(), $tenant->getDbConfig());
        $this->assertSame(TenantFactory::DEFAULT_LANGUAGE, $tenant->getDefaultLanguage());
        $this->assertSame(TenantFactory::DEFAULT_CURRENCY, $tenant->getDefaultCurrency());
        $this->assertSame(TenantFactory::TENANT_STATUS_TEST, $tenant->getStatus());
        $this->assertInstanceOf(DateTime::class, $tenant->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $tenant->getUpdatedAt());
        $this->assertEquals($tenant->getCreatedAt(), $tenant->getUpdatedAt());
        $this->assertFalse($tenant->isAvailable());
        $this->assertSame($datetime, $tenant->getDeletedAt());

        $tenant->setDefaultLanguage(TenantFactory::LANGUAGE_EN);
        $this->assertSame(TenantFactory::LANGUAGE_EN, $tenant->getDefaultLanguage());

        $tenant->setDefaultCurrency(TenantFactory::CURRENCY_EUR);
        $this->assertSame(TenantFactory::CURRENCY_EUR, $tenant->getDefaultCurrency());

        $tenant->setStatus(TenantFactory::TENANT_STATUS_LIVE);
        $this->assertSame(TenantFactory::TENANT_STATUS_LIVE, $tenant->getStatus());
    }

    public function testChangeAvailability(): void
    {
        $tenant = new Tenant(
            id: TenantFactory::TENANT_ID,
            companyId: TenantFactory::COMPANY_ID,
            domain: TenantFactory::DOMAIN,
            dbConfig: TenantFactory::getConfig(),
            defaultLanguage: TenantFactory::DEFAULT_LANGUAGE,
            defaultCurrency: TenantFactory::DEFAULT_CURRENCY,
            status: TenantFactory::TENANT_STATUS_TEST,
        );

        $this->assertFalse($tenant->isAvailable());

        $tenant->makeAvailable();

        $this->assertTrue($tenant->isAvailable());

        $tenant->makeUnavailable();

        $this->assertFalse($tenant->isAvailable());
    }

    public function testInstallNewApp(): void
    {
        $tenant = TenantFactory::getTenant();
        $appId = AppId::from(AppFactory::ALI_EXPRESS_ID);
        $this->assertNull($tenant->getApp($appId));

        $tenant->installApp($appId);
        /** @var App $installedApp */
        $installedApp = $tenant->getApp($appId);
        $this->assertInstanceOf(App::class, $installedApp);
        $this->assertSame($appId, $installedApp->getAppId());
        $this->assertTrue($installedApp->isInstalled());
        $this->assertFalse($installedApp->isActive());
        $this->assertSame(array_merge(AppFactory::APP_DEFAULT_CONFIG, ['isInstalled' => true]), $installedApp->getConfig());
    }

    public function testInstallNotInstalledButExistApp(): void
    {
        $tenant = TenantFactory::getTenant();
        $app = AppFactory::getApp(config: AppFactory::APP_DEFAULT_CONFIG);
        $tenant->populateApp($app);
        /** @var App $notInstalledApp */
        $notInstalledApp = $tenant->getApp($app->getAppId());
        $this->assertInstanceOf(App::class, $notInstalledApp);
        $this->assertFalse($notInstalledApp->isInstalled());

        $tenant->installApp($app->getAppId());
        /** @var App $installedApp */
        $installedApp = $tenant->getApp($app->getAppId());
        $this->assertInstanceOf(App::class, $installedApp);
        $this->assertTrue($installedApp->isInstalled());
        $this->assertSame($notInstalledApp->isActive(), $installedApp->isActive());
        $this->assertSame(array_merge($notInstalledApp->getConfig(), ['isInstalled' => true]), $installedApp->getConfig());
    }

    public function testPopulateApp(): void
    {
        $tenant = TenantFactory::getTenant();
        $app = AppFactory::getApp();
        $this->assertNull($tenant->getApps());

        $tenant->populateApp($app);
        /** @var App[] $apps */
        $apps = $tenant->getApps();
        $this->assertContainsOnlyInstancesOf(App::class, $apps);
        $this->assertCount(1, $apps);
        /** @var App $firstApp */
        $firstApp = current($apps);
        $this->assertSame($app->getAppId()->value, $firstApp->getAppId()->value);
        $this->assertSame($app->isInstalled(), $firstApp->isInstalled());
        $this->assertSame($app->isActive(), $firstApp->isActive());
        $this->assertSame($app->getConfig(), $firstApp->getConfig());
    }

    public function testRemoveApp(): void
    {
        $tenant = TenantFactory::getTenant();
        $appOne = AppFactory::getApp();
        $tenant->populateApp($appOne);
        /** @var App[] $apps */
        $apps = $tenant->getApps();
        $this->assertCount(1, $apps);
        $tenant->removeApp($appOne);

        $this->assertNull($tenant->getApps());
    }

    public function testGetApp(): void
    {
        $tenant = TenantFactory::getTenant();
        $app = AppFactory::getApp();
        $tenant->populateApp($app);
        /** @var App $foundApp */
        $foundApp = $tenant->getApp($app->getAppId());
        $this->assertInstanceOf(App::class, $foundApp);
        $this->assertSame($app->getAppId()->value, $foundApp->getAppId()->value);
    }

    public function testIsAppInstalled(): void
    {
        $tenant = TenantFactory::getTenant();
        $this->assertFalse($tenant->isAppInstalled(AppId::AliExpress));

        $tenant->installApp(AppId::AliExpress);
        $this->assertTrue($tenant->isAppInstalled(AppId::AliExpress));
    }

    public function testWithEmptyInitialDbConfigAnSettingDbConfig(): void
    {
        $tenant = new Tenant(
            id: TenantFactory::TENANT_ID,
            companyId: TenantFactory::COMPANY_ID,
            domain: TenantFactory::DOMAIN,
            dbConfig: '',
            defaultLanguage: TenantFactory::DEFAULT_LANGUAGE,
            defaultCurrency: TenantFactory::DEFAULT_CURRENCY,
            status: TenantFactory::TENANT_STATUS_TEST,
        );

        $this->assertEmpty($tenant->getDbConfig());
        $this->assertNull($tenant->getConfiguredAt());

        $tenant->setDbConfig(TenantFactory::getConfig());
        $this->assertNotEmpty($tenant->getDbConfig());
        $this->assertNotNull($tenant->getConfiguredAt());
    }
}
