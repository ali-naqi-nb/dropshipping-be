<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class OpenApiTest extends WebTestCase
{
    private const DOCS_PATH_NAME = 'docs';

    public function testAllEndpointsHaveOpenApiDocumentation(): void
    {
        $client = self::createClient();

        // fetch the routes
        /** @var RouterInterface $router */
        $router = self::getContainer()->get(RouterInterface::class);
        $this->assertNotNull($router, 'The router service should be available.');
        $routes = $router->getRouteCollection();
        $this->assertNotNull($routes, 'The route collection should be available.');

        // fetch OpenAPI documentation
        $docsPath = $router->generate(self::DOCS_PATH_NAME);
        $client->request('GET', $docsPath);
        $this->assertResponseStatusCodeSame(200);
        $json = $client->getResponse()->getContent();
        $this->assertNotEmpty($json, 'The OpenAPI documentation should not be empty.');
        $openApi = json_decode($json, true);
        $this->assertIsArray($openApi, 'The OpenAPI documentation should be a valid JSON.');
        $this->assertArrayHasKey('paths', $openApi, 'The OpenAPI documentation should contain paths.');

        // assert all routes are documented
        $errors = [];
        foreach ($routes->all() as $route) {
            $normalizedPath = rtrim($route->getPath(), '/');
            if (!isset($openApi['paths'][$normalizedPath]) && $docsPath !== $normalizedPath) {
                $errors[] = sprintf('Endpoint %s is not documented', $route->getPath());
            }
        }
        $this->assertEmpty($errors, implode("\n", $errors));
    }
}
