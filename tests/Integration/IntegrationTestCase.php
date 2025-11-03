<?php

declare(strict_types=1);

namespace App\Tests\Integration;

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
use Monolog\Handler\TestHandler;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class IntegrationTestCase extends KernelTestCase
{
    use PHPMatcherAssertions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRedisTenantConnection();
    }

    protected function assertLog(string $expectedLevel, string $expectedMessage, array $expectedContext = []): void
    {
        /** @var TestHandler $logger */
        $logger = self::getContainer()->get('monolog.handler.test');
        $logs = $logger->getRecords();

        $this->assertCount(1, $logs);
        $this->assertSame($expectedLevel, $logs[0]['level_name']);
        $this->assertSame($expectedMessage, $logs[0]['message']);
        $this->assertMatchesPattern($expectedContext, $logs[0]['context']);
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

    protected function createMockHttpResponse(int $statusCode, array $responseData): ResponseInterface
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn($statusCode);
        $responseMock->method('toArray')->willReturn($responseData);

        return $responseMock;
    }
}
