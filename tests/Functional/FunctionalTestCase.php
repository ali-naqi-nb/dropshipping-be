<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Persistence\Connection\RedisTenantConnection;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\Transport\RpcResultReceiverInterface;
use App\Infrastructure\Rpc\Transport\RpcResultSenderInterface;
use App\Tests\Double\Rpc\Transport\MockResultReceiver;
use App\Tests\Double\Rpc\Transport\MockResultSender;
use App\Tests\Shared\Factory\DbConfigFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Random\Generator;
use Closure;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use DateTime;
use Doctrine\DBAL\Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class FunctionalTestCase extends WebTestCase
{
    use PHPMatcherAssertions;

    protected const METHOD = 'GET';
    protected const LOCALE = 'en_US';
    protected const ROUTE = '';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    protected function getDecodedJsonResponse(): ?array
    {
        /** @var string $responseContent */
        $responseContent = $this->client->getResponse()->getContent();
        /** @var array $response */
        $response = json_decode($responseContent, true);

        return $response;
    }

    /**
     * @param array<string, mixed>  $pathParams
     * @param array<string, mixed>  $queryParams
     * @param array<string, mixed>  $data
     * @param array<string, string> $headers
     */
    protected function makePostRequest(
        string $method,
        array $pathParams = [],
        array $queryParams = [],
        array $data = [],
        array $headers = []
    ): Crawler {
        return $this->client->jsonRequest(
            method: $method,
            uri: $this->buildUrl($pathParams, $queryParams),
            parameters: $data,
            server: $headers,
        );
    }

    protected function makeTenantRequest(
        string $method,
        array $pathParams = [],
        array $queryParams = [],
        array $data = [],
        array $headers = [],
        string $tenantId = TenantFactory::TENANT_ID,
        ?string $route = null,
    ): Crawler {
        return $this->client->jsonRequest(
            method: $method,
            uri: $this->buildUrl($pathParams, $queryParams, $route),
            parameters: $data,
            server: array_merge($headers, ['HTTP_X_TENANT_ID' => $tenantId]),
        );
    }

    protected function buildUrl(array $pathParams = [], array $queryParams = [], ?string $route = null): string
    {
        if (!isset($pathParams['_locale'])) {
            $pathParams['_locale'] = static::LOCALE;
        }
        $pathKeys = [];
        $pathValues = [];
        foreach ($pathParams as $key => $value) {
            $pathKeys[] = '{'.$key.'}';
            $pathValues[] = $value;
        }
        $uri = str_replace($pathKeys, $pathValues, $route ?? static::ROUTE);

        if ([] === $queryParams) {
            return $uri;
        }

        return $uri.'?'.http_build_query($queryParams);
    }

    protected function createRedisTenantConnection(string $tenantId = TenantFactory::TENANT_ID): RedisTenantConnection
    {
        /** @var TenantStorageInterface $tenantStorage */
        $tenantStorage = self::getContainer()->get(TenantStorageInterface::class);
        $tenantStorage->setId($tenantId);

        /** @var RedisTenantConnection $redisConnection */
        $redisConnection = self::getContainer()->get(RedisTenantConnection::class);
        $redisConnection->connect();

        return $redisConnection;
    }

    /**
     * @throws Exception
     */
    protected function createDoctrineTenantConnection(): DoctrineTenantConnection
    {
        /** @var DoctrineTenantConnection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.tenant_connection');

        $connection->create(DbConfigFactory::getDbConfig());
        $connection->beginTransaction();

        return $connection;
    }

    protected function assertResponseSuccess(?array $expectedData, int $statusCode = 200): void
    {
        $this->assertResponseStatusCodeSame($statusCode);
        $this->assertMatchesPattern(['data' => $expectedData], $this->getDecodedJsonResponse());
    }

    protected function assertResponseNotFound(string $message = 'Not Found'): void
    {
        $this->assertResponseStatusCodeSame(404);
        $this->assertMatchesPattern(['message' => $message], $this->getDecodedJsonResponse());
    }

    protected function assertResponseErrors(array $expectedErrors, int $statusCode = 400): void
    {
        $this->assertResponseStatusCodeSame($statusCode);
        $this->assertMatchesPattern(['errors' => $expectedErrors], $this->getDecodedJsonResponse());
    }

    protected function setAeAccessToken(): void
    {
        $now = new DateTime();

        /** @var TenantRepositoryInterface $tenantRepository */
        $tenantRepository = self::getContainer()->get(TenantRepositoryInterface::class);

        /** @var Tenant $tenant */
        $tenant = $tenantRepository->findOneById(TenantFactory::TENANT_ID);
        /** @var App $app */
        $app = $tenant->getApp(AppId::AliExpress);
        $app->setConfig(array_merge($app->getConfig(), [
            'accessToken' => Generator::string(),
            'refreshToken' => Generator::string(),
            'accessTokenExpireAtTimeStamp' => 3600 + $now->getTimestamp(),
            'refreshTokenExpireAtTimeStamp' => 7200 + $now->getTimestamp(),
        ]));
        $tenant->populateApp($app);
        $tenantRepository->save($tenant);
    }

    protected function mockRpcResponse(Closure $matchCallback, RpcResult $response): void
    {
        $rpcResultReceiver = self::getContainer()->get(RpcResultReceiverInterface::class);

        if (!$rpcResultReceiver instanceof MockResultReceiver) {
            throw new RuntimeException('RpcResultReceiver must be an instance of MockResultReceiver');
        }

        $rpcResultReceiver->mock($matchCallback, $response);
    }

    protected function tearDown(): void
    {
        $rpcResultSender = self::getContainer()->get(RpcResultReceiverInterface::class);

        if ($rpcResultSender instanceof MockResultReceiver) {
            $rpcResultSender->clear();
        }

        $rpcResultSender = self::getContainer()->get(RpcResultSenderInterface::class);

        if ($rpcResultSender instanceof MockResultSender) {
            $rpcResultSender->clear();
        }

        parent::tearDown();
    }
}
