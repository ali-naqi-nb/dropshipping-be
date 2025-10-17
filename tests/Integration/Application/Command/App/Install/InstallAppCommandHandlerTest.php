<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\App\Install;

use App\Application\Command\App\Install\InstallAppCommand;
use App\Application\Command\App\Install\InstallAppCommandHandler;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Error\ErrorType;
use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

final class InstallAppCommandHandlerTest extends IntegrationTestCase
{
    private InstallAppCommandHandler $handler;
    private TenantRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var InstallAppCommandHandler $handler */
        $handler = self::getContainer()->get(InstallAppCommandHandler::class);
        $this->handler = $handler;

        /** @var TenantRepositoryInterface $repository */
        $repository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->repository = $repository;
    }

    public function testInstallAppWorksCorrectlyReturnsNull(): void
    {
        $command = new InstallAppCommand(TenantFactory::TENANT_FOR_DELETE_ID, AppFactory::ALI_EXPRESS_ID);
        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($command->getTenantId());

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertNull($tenant->getApp(AppId::from($command->getAppId())));

        $response = $this->handler->__invoke($command);
        $this->assertNull($response);

        /** @var array $apps */
        $apps = $tenant->getApps();
        $this->assertCount(1, $apps);

        $this->assertNull($tenant->getConfiguredAt());

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async_create_db_app_installed');
        $this->assertCount(1, $transport->getSent());

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($command->getTenantId());
        /** @var App $app */
        $app = $tenant->getApp(AppId::from($command->getAppId()));

        $this->assertInstanceOf(App::class, $app);
        $this->assertTrue($app->isInstalled());
        $this->assertFalse($app->isActive());
        $this->assertSame(array_merge(AppFactory::APP_DEFAULT_CONFIG, ['isInstalled' => true]), $app->getConfig());
    }

    public function testInstallAppAlreadyInstalledAppWorksCorrectlyReturnsNull(): void
    {
        $command = new InstallAppCommand(TenantFactory::SECOND_TENANT_ID, AppFactory::ALI_EXPRESS_ID);
        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($command->getTenantId());
        $this->assertInstanceOf(Tenant::class, $tenant);

        $app = AppId::from($command->getAppId());
        $tenant->installApp($app);
        $this->repository->save($tenant);

        $this->assertNotNull($tenant->getApp(AppId::from($command->getAppId())));

        $response = $this->handler->__invoke($command);
        $this->assertNull($response);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($command->getTenantId());
        /** @var App $app */
        $savedApp = $tenant->getApp(AppId::from($command->getAppId()));

        $this->assertInstanceOf(App::class, $savedApp);
        $this->assertTrue($savedApp->isInstalled());
        $this->assertFalse($savedApp->isActive());
        $this->assertSame(array_merge(AppFactory::APP_DEFAULT_CONFIG, ['isInstalled' => true]), $savedApp->getConfig());
    }

    public function testInstallAppWithNonExistTenantReturnsErrorResponse(): void
    {
        $command = new InstallAppCommand(TenantFactory::NON_EXISTING_TENANT_ID, AppFactory::ALI_EXPRESS_ID);

        $this->assertNull($this->repository->findOneById($command->getTenantId()));

        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
    }

    public function testInstallAppWithNotSupportedAppReturn422(): void
    {
        $command = new InstallAppCommand(TenantFactory::TENANT_ID, AppFactory::APP_ID_NOT_SUPPORTED);
        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::Error, $response->getType());
        $this->assertSame(['appId' => 'App "'.$command->getAppId().'" is not supported.'], $response->getErrors());
    }
}
