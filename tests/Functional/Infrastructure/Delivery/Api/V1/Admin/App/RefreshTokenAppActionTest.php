<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class RefreshTokenAppActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}/refresh-token';
    protected const AUTH_ROUTE = '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}/exchange-token';
    protected const METHOD = 'POST';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUserHeaders();
    }

    /**
     * @dataProvider provideValidData
     */
    public function testRefreshTokenAppReturns200(string $tenantId, string $appId): void
    {
        // Authorize app first
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => $tenantId, 'appId' => $appId],
            route: self::AUTH_ROUTE
        );

        $this->assertResponseSuccess([
            'appId' => $appId,
            'config' => [
                'isActive' => '@boolean@',
                'isInstalled' => true,
                'accessToken' => '@string@',
                'refreshToken' => '@string@',
                'accessTokenExpireAtTimeStamp' => '@integer@',
                'refreshTokenExpireAtTimeStamp' => '@integer@',
                'clientId' => '@integer@',
                'sellerId' => '@string@',
            ],
        ]);

        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => $tenantId, 'appId' => $appId]
        );

        $this->assertResponseSuccess([
            'appId' => $appId,
            'config' => [
                'isActive' => '@boolean@',
                'isInstalled' => true,
                'accessToken' => '@string@',
                'refreshToken' => '@string@',
                'accessTokenExpireAtTimeStamp' => '@integer@',
                'refreshTokenExpireAtTimeStamp' => '@integer@',
                'clientId' => '@integer@',
                'sellerId' => '@string@',
            ],
        ]);
    }

    public function testRefreshTokenAppNonExistingTenantReturns404(): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => TenantFactory::NON_EXISTING_TENANT_ID, 'appId' => AppFactory::ALI_EXPRESS_ID]
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testRefreshTokenAppNotSupportedAppReturns422(): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => TenantFactory::TENANT_ID, 'appId' => AppFactory::APP_ID_NOT_SUPPORTED]
        );

        self::assertResponseStatusCodeSame(422);
        $this->assertMatchesPattern(['errors' => ['appId' => 'App "'.AppFactory::APP_ID_NOT_SUPPORTED.'" is not supported.']], $this->getDecodedJsonResponse());
    }

    public function testRefreshTokenAppNotInstalledReturns422(): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => TenantFactory::TENANT_FOR_DELETE_ID, 'appId' => AppFactory::ALI_EXPRESS_ID]
        );

        self::assertResponseStatusCodeSame(422);
        $this->assertMatchesPattern(['errors' => ['appId' => 'App "'.AppFactory::ALI_EXPRESS_ID.'" is either not installed or not active.']], $this->getDecodedJsonResponse());
    }

    public function provideValidData(): array
    {
        return [
            'ali-express' => [
                TenantFactory::TENANT_ID,
                AppFactory::ALI_EXPRESS_ID,
            ],
        ];
    }
}
