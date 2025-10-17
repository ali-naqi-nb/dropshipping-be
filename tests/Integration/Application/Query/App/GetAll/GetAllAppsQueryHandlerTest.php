<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Query\App\GetAll;

use App\Application\Query\App\GetAll\GetAllAppsQuery;
use App\Application\Query\App\GetAll\GetAllAppsQueryHandler;
use App\Application\Shared\Error\ErrorResponse;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\TenantFactory;

final class GetAllAppsQueryHandlerTest extends IntegrationTestCase
{
    private GetAllAppsQueryHandler $handler;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var GetAllAppsQueryHandler $handler */
        $handler = self::getContainer()->get(GetAllAppsQueryHandler::class);
        $this->handler = $handler;
    }

    public function testInvokeReturnsAllApps(): void
    {
        $response = $this->handler->__invoke(new GetAllAppsQuery(TenantFactory::TENANT_ID));

        $this->assertIsArray($response);
    }

    public function testInvokeReturnsErrorWhenTenantNotFound(): void
    {
        $response = $this->handler->__invoke(new GetAllAppsQuery(TenantFactory::NON_EXISTING_TENANT_ID));

        $this->assertInstanceOf(ErrorResponse::class, $response);
    }
}
