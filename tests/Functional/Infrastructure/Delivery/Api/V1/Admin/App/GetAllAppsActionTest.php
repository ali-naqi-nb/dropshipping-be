<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class GetAllAppsActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps';
    protected const METHOD = 'GET';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUserHeaders();
    }

    /**
     * @dataProvider provideValidData
     */
    public function testGetAllAppsReturns200(string $tenantId, array $apps): void
    {
        $route = str_replace(['{_locale}', '{tenantId}'], [self::LOCALE, $tenantId], self::ROUTE);
        $this->client->jsonRequest(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(200);

        $expectedResponse = ['data' => ['items' => $apps]];

        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse());
    }

    public function testGetAllAppsReturns404(): void
    {
        $route = str_replace(['{_locale}', '{tenantId}'], [self::LOCALE, TenantFactory::NON_EXISTING_TENANT_ID], self::ROUTE);
        $this->client->jsonRequest(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(404);
        $this->assertSame(['message' => 'Tenant not found'], $this->getDecodedJsonResponse());
    }

    public function provideValidData(): array
    {
        return [
            'configuredApps' => [
                TenantFactory::TENANT_ID,
                [
                    [
                        'appId' => AppFactory::ALI_EXPRESS_ID,
                        'config' => AppFactory::ALI_EXPRESS_CONFIG,
                    ],
                ],
            ],
            'unconfiguredApps' => [
                TenantFactory::SECOND_TENANT_ID,
                [
                    [
                        'appId' => AppFactory::ALI_EXPRESS_ID,
                        'config' => AppFactory::ALI_EXPRESS_NOT_INSTALLED_AND_NOT_ACTIVE_CONFIG,
                    ],
                ],
            ],
        ];
    }
}
