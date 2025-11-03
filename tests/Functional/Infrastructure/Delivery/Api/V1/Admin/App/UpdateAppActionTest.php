<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class UpdateAppActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}';
    protected const METHOD = 'PUT';

    private const ROUTE_PLACEHOLDERS = ['{_locale}', '{tenantId}', '{appId}'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUserHeaders();
    }

    public function testUpdateAppReturns204(): void
    {
        $data = [
            'config' => AppFactory::ALI_EXPRESS_CONFIG,
        ];

        $route = str_replace(
            self::ROUTE_PLACEHOLDERS,
            [self::LOCALE, TenantFactory::TENANT_ID, AppFactory::ALI_EXPRESS_ID],
            self::ROUTE
        );
        $this->client->jsonRequest(self::METHOD, $route, $data);

        self::assertResponseStatusCodeSame(200);

        $expectedResponse = [
            'data' => [
                'appId' => AppFactory::ALI_EXPRESS_ID,
                'config' => AppFactory::ALI_EXPRESS_CONFIG,
            ],
        ];

        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse());
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testUpdateAppWithInvalidDataReturns400(array $data, array $errors): void
    {
        $route = str_replace(
            self::ROUTE_PLACEHOLDERS,
            [self::LOCALE, TenantFactory::TENANT_ID, AppFactory::ALI_EXPRESS_ID],
            self::ROUTE
        );
        $this->client->jsonRequest(self::METHOD, $route, $data);

        self::assertResponseStatusCodeSame(400);
        $this->assertSame(['errors' => $errors], $this->getDecodedJsonResponse());
    }

    public function testUpdateAppNonExistingTenantReturns404(): void
    {
        $route = str_replace(
            self::ROUTE_PLACEHOLDERS,
            [self::LOCALE, TenantFactory::NON_EXISTING_TENANT_ID, AppFactory::ALI_EXPRESS_ID],
            self::ROUTE
        );
        $this->client->jsonRequest(self::METHOD, $route, ['config' => AppFactory::NEW_CONFIG]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testUpdateAppWithNotSupportedAppReturns422(): void
    {
        $data = [
            'config' => AppFactory::NEW_CONFIG,
        ];

        $route = str_replace(
            self::ROUTE_PLACEHOLDERS,
            [self::LOCALE, TenantFactory::TENANT_ID, AppFactory::APP_ID_NOT_SUPPORTED],
            self::ROUTE
        );
        $this->client->jsonRequest(self::METHOD, $route, $data);

        self::assertResponseStatusCodeSame(422);
        $this->assertMatchesPattern(['errors' => ['appId' => 'App "'.AppFactory::APP_ID_NOT_SUPPORTED.'" is not supported.']], $this->getDecodedJsonResponse());
    }

    public function provideInvalidData(): array
    {
        return [
            'missingRequiredData' => [
                [],
                [
                    'config' => 'This value should not be blank.',
                ],
            ],
            'missingIsActive' => [
                [
                    'config' => [
                        'additionalField' => 'value',
                    ],
                ],
                [
                    'config.isActive' => 'This field is missing.',
                ],
            ],
            'isActiveWrongDataType' => [
                [
                    'config' => [
                        'isActive' => 1,
                    ],
                ],
                [
                    'config.isActive' => 'This value should be of type boolean.',
                ],
            ],
        ];
    }
}
