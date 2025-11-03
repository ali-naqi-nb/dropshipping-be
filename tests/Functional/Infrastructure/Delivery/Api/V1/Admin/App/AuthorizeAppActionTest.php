<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class AuthorizeAppActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}/exchange-token';
    protected const METHOD = 'POST';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUserHeaders();
    }

    /**
     * @dataProvider provideValidData
     */
    public function testAuthorizeAppReturns200(string $tenantId, string $appId, string $token): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => $tenantId, 'appId' => $appId],
            data: ['token' => $token]
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

    public function testAuthorizeAppNonExistingTenantReturns404(): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => TenantFactory::NON_EXISTING_TENANT_ID, 'appId' => AppFactory::ALI_EXPRESS_ID],
            data: ['token' => AppFactory::ALI_EXPRESS_TOKEN]
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testInstallAppReturn422(): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => TenantFactory::TENANT_FOR_DELETE_ID, 'appId' => AppFactory::ALI_EXPRESS_ID],
            data: ['token' => AppFactory::ALI_EXPRESS_TOKEN]
        );

        self::assertResponseStatusCodeSame(422);
        $this->assertMatchesPattern(['errors' => ['appId' => 'App "'.AppFactory::ALI_EXPRESS_ID.'" is either not installed or not active.']], $this->getDecodedJsonResponse());
    }

    public function testManagerFailureReturns422(): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['tenantId' => TenantFactory::TENANT_ID, 'appId' => AppFactory::ALI_EXPRESS_ID],
            data: ['token' => AppFactory::ALI_EXPRESS_FAILED_TOKEN]
        );

        $this->assertResponseErrors(['common' => 'Failed to get access token.'], 422);
    }

    public function provideValidData(): array
    {
        return [
            'ali-express' => [
                TenantFactory::TENANT_ID,
                AppFactory::ALI_EXPRESS_ID,
                AppFactory::ALI_EXPRESS_TOKEN,
            ],
        ];
    }
}
