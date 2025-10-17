<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class GetAppActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}';
    protected const METHOD = 'GET';

    private const ROUTE_PLACEHOLDERS = ['{_locale}', '{tenantId}', '{appId}'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUserHeaders();
    }

    public function testGetAppReturns200(): void
    {
        $route = str_replace(
            self::ROUTE_PLACEHOLDERS,
            [self::LOCALE, TenantFactory::TENANT_ID, AppFactory::ALI_EXPRESS_ID],
            self::ROUTE
        );
        $this->client->jsonRequest(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(200);

        $expectedResponse = [
            'data' => [
                'appId' => AppFactory::ALI_EXPRESS_ID,
                'config' => AppFactory::ALI_EXPRESS_CONFIG,
            ],
        ];

        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse());
    }

    public function testGetAppNonExistingTenantReturns404(): void
    {
        $route = str_replace(
            self::ROUTE_PLACEHOLDERS,
            [self::LOCALE, TenantFactory::NON_EXISTING_TENANT_ID, AppFactory::ALI_EXPRESS_ID],
            self::ROUTE
        );
        $this->client->request(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetAppWithMissingAppInTenantReturns404(): void
    {
        $route = str_replace(
            self::ROUTE_PLACEHOLDERS,
            [self::LOCALE, TenantFactory::SECOND_TENANT_ID, AppFactory::ALI_EXPRESS_ID],
            self::ROUTE
        );
        $this->client->request(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetAppWithNonSupportedAppReturns422(): void
    {
        $route = str_replace(
            self::ROUTE_PLACEHOLDERS,
            [self::LOCALE, TenantFactory::TENANT_ID, AppFactory::APP_ID_NOT_SUPPORTED],
            self::ROUTE
        );
        $this->client->request(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(422);
        $this->assertMatchesPattern(['errors' => ['appId' => 'App "'.AppFactory::APP_ID_NOT_SUPPORTED.'" is not supported.']], $this->getDecodedJsonResponse());
    }
}
