<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\App;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Symfony\Component\HttpFoundation\Response;

final class DeleteAppActionTest extends FunctionalTestCase
{
    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/tenants/{id}/apps/{appId}';
    protected const METHOD = 'DELETE';

    public function testDeleteAppWorksCorrectlyReturns204(): void
    {
        $route = str_replace(['{_locale}', '{id}', '{appId}'], [self::LOCALE, TenantFactory::TENANT_ID, AppFactory::ALI_EXPRESS_ID], self::ROUTE);
        $this->client->jsonRequest(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteUnSupportedAppReturns422(): void
    {
        $route = str_replace(['{_locale}', '{id}', '{appId}'], [self::LOCALE, TenantFactory::TENANT_FOR_DELETE_ID, AppFactory::APP_ID_NOT_SUPPORTED], self::ROUTE);
        $this->client->jsonRequest(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertMatchesPattern([
            'errors' => [
                'appId' => sprintf('App "%s" is not supported.', AppFactory::APP_ID_NOT_SUPPORTED),
            ],
        ], $this->getDecodedJsonResponse());
    }

    public function testDeleteAppWithNonExistingTenantReturns404(): void
    {
        $route = str_replace(['{_locale}', '{id}', '{appId}'], [self::LOCALE, TenantFactory::NON_EXISTING_TENANT_ID, AppFactory::ALI_EXPRESS_ID], self::ROUTE);
        $this->client->jsonRequest(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertMatchesPattern([
            'message' => 'Tenant not found',
        ], $this->getDecodedJsonResponse());
    }

    public function testDeleteAppWithMissingAppInTenantReturns404(): void
    {
        $route = str_replace(['{_locale}', '{id}', '{appId}'], [self::LOCALE, TenantFactory::SECOND_TENANT_ID, AppFactory::ALI_EXPRESS_ID], self::ROUTE);
        $this->client->jsonRequest(self::METHOD, $route);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertMatchesPattern([
            'message' => 'App not found',
        ], $this->getDecodedJsonResponse());
    }
}
