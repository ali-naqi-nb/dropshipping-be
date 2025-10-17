<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1;

use App\Infrastructure\Delivery\Api\V1\GetDocumentationAction;
use App\Tests\Functional\FunctionalTestCase;

final class GetDocumentationActionTest extends FunctionalTestCase
{
    protected const ROUTE = '/{path_prefix}/v1/docs';
    protected const METHOD = 'GET';
    protected const HTTP_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];

    protected const WRONG_ROUTE = '/dropshipping/wrong-route-for-testing';

    public function testGetDocsReturnsJson(): void
    {
        $this->client->request(self::METHOD, $this->getRoute());

        $this->assertResponseStatusCodeSame(200);
        $expectedResponse = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'NEXT BASKET Dropshipping manager service',
                'version' => '0.1',
            ],
            'paths' => [
                '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}' => [
                    'post' => '@array@',
                    'delete' => '@array@',
                    'put' => '@array@',
                    'get' => '@array@',
                ],
                '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps' => [
                    'get' => '@array@',
                ],
                '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}/exchange-token' => [
                    'post' => '@array@',
                ],
                '/dropshipping/admin/v1/{_locale}/tenants/{tenantId}/apps/{appId}/refresh-token' => [
                    'post' => '@array@',
                ],
                '/dropshipping/admin/v1/{_locale}/aliexpress-product-import' => [
                    'post' => '@array@',
                ],
                '/dropshipping/v1/jsonrpc' => [
                    'post' => '@array@',
                ],
                '/dropshipping/admin/v1/{_locale}/aliexpress-product-group' => [
                    'post' => '@array@',
                ],
                '/dropshipping/v1/{_locale}/aliexpress/orders/webhook' => [
                    'post' => '@array@',
                ],
            ],
            'components' => '@array@',
            'tags' => '@array@',
        ];

        $this->assertMatchesPattern($expectedResponse, $this->getDecodedJsonResponse());
    }

    public function testRouteWithInvalidHttpMethodReturnsNotFound(): void
    {
        foreach (self::HTTP_METHODS as $method) {
            if (self::METHOD === $method) {
                continue;
            }

            $this->client->request($method, $this->getRoute());
            $this->assertResponseStatusCodeSame(405);
        }
    }

    public function testWrongRouteReturnsNotFound(): void
    {
        $this->client->request('GET', self::WRONG_ROUTE);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUnknownExceptionWillBeHandled(): void
    {
        $container = self::getContainer();

        $mock = $this->createMock(GetDocumentationAction::class);

        $mock->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException(new \Exception()));
        $container->set(GetDocumentationAction::class, $mock);

        $this->client->request(self::METHOD, $this->getRoute());

        $this->assertResponseErrors(['message' => 'Internal Server Error'], 500);
    }

    public function testJsonExceptionWillBeHandled(): void
    {
        $container = self::getContainer();

        $mock = $this->createMock(GetDocumentationAction::class);

        $mock->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException(new \JsonException()));
        $container->set(GetDocumentationAction::class, $mock);

        $this->client->request(self::METHOD, $this->getRoute());

        $this->assertResponseErrors(['message' => 'Bad Request']);
    }

    public function getRoute(): string
    {
        /** @var string $pathPrefix */
        $pathPrefix = self::getContainer()->getParameter('app.path_prefix');

        return str_replace('{path_prefix}', $pathPrefix, self::ROUTE);
    }
}
