<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\App\Delete;

use App\Application\Command\App\Delete\DeleteAppCommand;
use App\Application\Command\App\Delete\DeleteAppCommandHandler;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Error\ErrorType;
use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;

final class DeleteAppCommandHandlerTest extends IntegrationTestCase
{
    private DeleteAppCommandHandler $handler;
    private TenantRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TenantRepositoryInterface $repository */
        $repository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->repository = $repository;

        /** @var DeleteAppCommandHandler $handler */
        $handler = self::getContainer()->get(DeleteAppCommandHandler::class);
        $this->handler = $handler;
    }

    public function testDeleteActiveAppWorksCorrectlyDispatchMessageAndReturnsNull(): void
    {
        $command = new DeleteAppCommand(TenantFactory::TENANT_FOR_DELETE_ID, AppFactory::ALI_EXPRESS_ID);

        $tenant = $this->repository->findOneById($command->getTenantId());
        $this->assertInstanceOf(Tenant::class, $tenant);

        $appId = AppId::from($command->getAppId());
        $app = AppFactory::getApp();

        $tenant->populateApp($app);
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame($app->getAppId(), $appId);
        $this->assertNull($this->handler->__invoke($command));

        $tenant = $this->repository->findOneById($command->getTenantId());
        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertNull($tenant->getApp($appId));
    }

    public function testDeleteAppWithNotSupportedAppReturnsErrorResponse(): void
    {
        $command = new DeleteAppCommand(TenantFactory::TENANT_ID, AppFactory::APP_ID_NOT_SUPPORTED);

        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::Error, $response->getType());
        $this->assertSame(['appId' => 'App "'.$command->getAppId().'" is not supported.'], $response->getErrors());
    }

    public function testDeleteAppWithNonExistTenantReturnsErrorResponse(): void
    {
        $command = new DeleteAppCommand(TenantFactory::NON_EXISTING_TENANT_ID, AppFactory::ALI_EXPRESS_ID);

        $this->assertNull($this->repository->findOneById($command->getTenantId()));
        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
    }

    public function testDeleteAppWithMissingAppInTenantReturnsErrorResponse(): void
    {
        $command = new DeleteAppCommand(TenantFactory::SECOND_TENANT_ID, AppFactory::ALI_EXPRESS_ID);

        $tenant = $this->repository->findOneById($command->getTenantId());
        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertNull($tenant->getApp(AppId::from($command->getAppId())));

        $response = $this->handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
    }
}
