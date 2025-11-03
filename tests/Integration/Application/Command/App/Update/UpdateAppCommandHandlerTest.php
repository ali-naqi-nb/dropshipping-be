<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\App\Update;

use App\Application\Command\App\Update\UpdateAppCommand;
use App\Application\Command\App\Update\UpdateAppCommandHandler;
use App\Application\Shared\App\AppResponse;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Error\ErrorType;
use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;

final class UpdateAppCommandHandlerTest extends IntegrationTestCase
{
    private UpdateAppCommandHandler $handler;
    private TenantRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var UpdateAppCommandHandler $handler */
        $handler = self::getContainer()->get(UpdateAppCommandHandler::class);
        $this->handler = $handler;

        /** @var TenantRepositoryInterface $repository */
        $repository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->repository = $repository;
    }

    public function testUpdateAppWorksCorrectlyReturnsNull(): void
    {
        $command = new UpdateAppCommand(TenantFactory::TENANT_ID, AppFactory::ALI_EXPRESS_ID, AppFactory::NEW_CONFIG_2);

        $tenant = $this->repository->findOneById($command->getTenantId());
        $this->assertInstanceOf(Tenant::class, $tenant);

        $appId = AppId::from($command->getAppId());
        $app = $tenant->getApp($appId);
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame($app->getAppId(), $appId);
        $this->assertEquals(AppFactory::NEW_CONFIG, $app->getConfig());

        /** @var AppResponse $response */
        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(AppResponse::class, $response);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($command->getTenantId());
        /** @var App $app */
        $app = $tenant->getApp(AppId::from($command->getAppId()));

        $this->assertInstanceOf(App::class, $app);
        $this->assertTrue($app->isInstalled());
        $this->assertFalse($app->isActive());
        $this->assertSame(AppFactory::NEW_CONFIG_2, $app->getConfig());

        $expectedConfig = array_merge(
            AppFactory::NEW_CONFIG_2,
            ['clientId' => '@integer@']
        );

        $this->assertSame(AppFactory::ALI_EXPRESS_ID, $response->getAppId());
        $this->assertMatchesPattern($expectedConfig, $response->getConfig());
    }

    public function testInstallAppWithValidationError(): void
    {
        $command = new UpdateAppCommand(TenantFactory::NON_EXISTING_TENANT_ID, AppFactory::ALI_EXPRESS_ID, array_merge(AppFactory::ALI_EXPRESS_CONFIG, ['isActive' => 123]));

        $this->assertNull($this->repository->findOneById($command->getTenantId()));

        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
    }

    public function testInstallAppWithNonExistTenantReturnsErrorResponse(): void
    {
        $command = new UpdateAppCommand(TenantFactory::NON_EXISTING_TENANT_ID, AppFactory::ALI_EXPRESS_ID, AppFactory::ALI_EXPRESS_CONFIG);

        $this->assertNull($this->repository->findOneById($command->getTenantId()));

        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
    }

    public function testUpdateAppWithNotSupportedAppReturnsErrorResponse(): void
    {
        $command = new UpdateAppCommand(
            TenantFactory::TENANT_ID,
            AppFactory::APP_ID_NOT_SUPPORTED,
            AppFactory::INSTALLED_AND_ACTIVATED_CONFIG,
        );

        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::Error, $response->getType());
        $this->assertSame(['appId' => 'App "'.$command->getAppId().'" is not supported.'], $response->getErrors());
    }

    public function testUpdateAppWithNonExistTenantReturnsErrorResponse(): void
    {
        $command = new UpdateAppCommand(
            TenantFactory::NON_EXISTING_TENANT_ID,
            AppFactory::ALI_EXPRESS_ID,
            AppFactory::INSTALLED_AND_ACTIVATED_CONFIG,
        );
        $this->assertNull($this->repository->findOneById($command->getTenantId()));

        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
    }

    public function testUpdateAppWithMissingAppInTenantReturnsErrorResponse(): void
    {
        $command = new UpdateAppCommand(
            TenantFactory::SECOND_TENANT_ID,
            AppFactory::ALI_EXPRESS_ID,
            AppFactory::INSTALLED_AND_ACTIVATED_CONFIG,
        );

        $tenant = $this->repository->findOneById($command->getTenantId());
        $this->assertInstanceOf(Tenant::class, $tenant);

        $appId = AppId::from($command->getAppId());
        $app = $tenant->getApp($appId);
        if (null !== $app) {
            $tenant->removeApp($app);
        }

        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
    }
}
