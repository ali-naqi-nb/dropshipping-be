<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Service\App\Aliexpress;

use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Exception\AliexpressAccessTokenManagerException;
use App\Infrastructure\Exception\TenantIdException;
use App\Infrastructure\Service\App\Aliexpress\AliexpressAccessTokenManager;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Random\Generator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AliexpressAccessTokenManagerTest extends IntegrationTestCase
{
    private HttpClientInterface&MockObject $clientMock;
    private ResponseInterface&MockObject $responseMock;
    private LoggerInterface&MockObject $loggerMock;
    private AliexpressAccessTokenManager $accessTokenManager;
    private TenantRepositoryInterface $tenantRepository;

    private AppId $appId;

    /**
     * @throws TenantIdException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(HttpClientInterface::class);

        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->loggerMock = $this->createMock(LoggerInterface::class);

        /** @var TenantRepositoryInterface $tenantRepository */
        $tenantRepository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->tenantRepository = $tenantRepository;

        $this->accessTokenManager = new AliexpressAccessTokenManager(
            appKey: '',
            appSecret: '',
            client: $this->clientMock,
            tenantRepository: $this->tenantRepository,
            logger: $this->loggerMock
        );

        $this->appId = AppId::AliExpress;
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testExchangeTemporaryTokenWithAccessTokenWorks(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);

        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertArrayNotHasKey('accessToken', $appConfig);
        $this->assertArrayNotHasKey('refreshToken', $appConfig);
        $this->assertArrayNotHasKey('accessTokenExpireAtTimeStamp', $appConfig);
        $this->assertArrayNotHasKey('refreshTokenExpireAtTimeStamp', $appConfig);

        $token = AppFactory::ALI_EXPRESS_TOKEN;
        $now = new DateTime();
        $expectedData = [
            'seller_id' => Generator::digitsOnlyString(),
            'access_token' => Generator::string(),
            'refresh_token' => Generator::string(),
            'expires_in' => 3600,
            'refresh_expires_in' => 7200,
        ];

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->responseMock->method('toArray')->willReturn($expectedData);

        $this->clientMock->method('request')->willReturn($this->responseMock);

        $returnedApp = $this->accessTokenManager->exchangeTemporaryTokenWithAccessToken(TenantFactory::TENANT_ID, $token);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->refresh($tenant);
        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertArrayHasKey('accessToken', $appConfig);
        $this->assertArrayHasKey('refreshToken', $appConfig);
        $this->assertArrayHasKey('accessTokenExpireAtTimeStamp', $appConfig);
        $this->assertArrayHasKey('refreshTokenExpireAtTimeStamp', $appConfig);

        $this->assertSame($expectedData['access_token'], $appConfig['accessToken']);
        $this->assertSame($expectedData['refresh_token'], $appConfig['refreshToken']);
        $this->assertSame($expectedData['expires_in'] + $now->getTimestamp(), $appConfig['accessTokenExpireAtTimeStamp']);
        $this->assertSame($expectedData['refresh_expires_in'] + $now->getTimestamp(), $appConfig['refreshTokenExpireAtTimeStamp']);

        $returnedAppConfig = $app->getConfig();
        $this->assertArrayHasKey('accessToken', $returnedAppConfig);
        $this->assertArrayHasKey('refreshToken', $returnedAppConfig);
        $this->assertArrayHasKey('accessTokenExpireAtTimeStamp', $returnedAppConfig);
        $this->assertArrayHasKey('refreshTokenExpireAtTimeStamp', $returnedAppConfig);

        $this->assertSame($expectedData['access_token'], $returnedAppConfig['accessToken']);
        $this->assertSame($expectedData['refresh_token'], $returnedAppConfig['refreshToken']);
        $this->assertSame($expectedData['expires_in'] + $now->getTimestamp(), $returnedAppConfig['accessTokenExpireAtTimeStamp']);
        $this->assertSame($expectedData['refresh_expires_in'] + $now->getTimestamp(), $returnedAppConfig['refreshTokenExpireAtTimeStamp']);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testExchangeTemporaryTokenWithExistingUserAccessTokenThrowsAliexpressAccessTokenManagerException(): void
    {
        $this->expectException(AliexpressAccessTokenManagerException::class);
        $this->expectExceptionMessage('This seller has already registered on the platform');

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);

        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertArrayNotHasKey('accessToken', $appConfig);
        $this->assertArrayNotHasKey('refreshToken', $appConfig);
        $this->assertArrayNotHasKey('accessTokenExpireAtTimeStamp', $appConfig);
        $this->assertArrayNotHasKey('refreshTokenExpireAtTimeStamp', $appConfig);

        $token = AppFactory::ALI_EXPRESS_TOKEN;
        $now = new DateTime();
        $expectedData = [
            'seller_id' => TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID,
            'access_token' => Generator::string(),
            'refresh_token' => Generator::string(),
            'expires_in' => 3600,
            'refresh_expires_in' => 7200,
        ];

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->responseMock->method('toArray')->willReturn($expectedData);

        $this->clientMock->method('request')->willReturn($this->responseMock);

        $returnedApp = $this->accessTokenManager->exchangeTemporaryTokenWithAccessToken(TenantFactory::TENANT_ID, $token);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testExchangeTemporaryTokenWithAccessTokenThrowsTenantIdException(): void
    {
        $this->expectException(TenantIdException::class);

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);
        $tenant->removeApp($app);

        $token = AppFactory::ALI_EXPRESS_TOKEN;
        $this->accessTokenManager->exchangeTemporaryTokenWithAccessToken(TenantFactory::TENANT_ID, $token);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testExchangeTemporaryTokenWithAccessTokenWillLogError(): void
    {
        $this->clientMock->method('request')->willThrowException($this->createMock(DecodingExceptionInterface::class));

        $this->loggerMock->expects($this->once())->method('error');

        $token = AppFactory::ALI_EXPRESS_TOKEN;
        $this->accessTokenManager->exchangeTemporaryTokenWithAccessToken(TenantFactory::TENANT_ID, $token);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testIsAccessTokenExpiredWorks(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);

        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertArrayNotHasKey('accessToken', $appConfig);
        $this->assertArrayNotHasKey('refreshToken', $appConfig);
        $this->assertArrayNotHasKey('accessTokenExpireAtTimeStamp', $appConfig);
        $this->assertArrayNotHasKey('refreshTokenExpireAtTimeStamp', $appConfig);

        $token = AppFactory::ALI_EXPRESS_TOKEN;
        $expectedData = [
            'seller_id' => Generator::digitsOnlyString(),
            'access_token' => Generator::string(),
            'refresh_token' => Generator::string(),
            'expires_in' => -5,
            'refresh_expires_in' => 3600,
        ];

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->responseMock->method('toArray')
            ->willReturnOnConsecutiveCalls(
                $expectedData,
                array_merge($expectedData, ['expires_in' => 3600])
            );

        $this->clientMock->method('request')->willReturn($this->responseMock);

        $this->accessTokenManager->exchangeTemporaryTokenWithAccessToken(TenantFactory::TENANT_ID, $token);
        $this->assertTrue($this->accessTokenManager->isAccessTokenExpired(TenantFactory::TENANT_ID));

        $this->accessTokenManager->exchangeTemporaryTokenWithAccessToken(TenantFactory::TENANT_ID, $token);
        $this->assertFalse($this->accessTokenManager->isAccessTokenExpired(TenantFactory::TENANT_ID));
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testRefreshAccessTokenWorks(): void
    {
        $now = new DateTime();
        $oldConfig = [
            'seller_id' => Generator::digitsOnlyString(),
            'accessToken' => Generator::string(),
            'refreshToken' => Generator::string(),
            'accessTokenExpireAtTimeStamp' => $now->getTimestamp() - 10,
            'refreshTokenExpireAtTimeStamp' => $now->getTimestamp() + 3600,
        ];

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);

        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);
        $app->setConfig(array_merge($app->getConfig(), $oldConfig));
        $tenant->populateApp($app);
        $this->tenantRepository->save($tenant);

        $expectedData = [
            'seller_id' => Generator::digitsOnlyString(),
            'access_token' => Generator::string(),
            'refresh_token' => Generator::string(),
            'expires_in' => 3600,
            'refresh_expires_in' => 7200,
        ];

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->responseMock->method('toArray')->willReturn($expectedData);

        $this->clientMock->method('request')->willReturn($this->responseMock);

        $returnedApp = $this->accessTokenManager->refreshAccessToken(TenantFactory::TENANT_ID);
        $this->assertNotNull($returnedApp);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->refresh($tenant);
        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertNotSame($oldConfig['accessToken'], $appConfig['accessToken']);
        $this->assertNotSame($oldConfig['refreshToken'], $appConfig['refreshToken']);
        $this->assertNotSame($oldConfig['accessTokenExpireAtTimeStamp'], $appConfig['accessTokenExpireAtTimeStamp']);
        $this->assertNotSame($oldConfig['refreshTokenExpireAtTimeStamp'], $appConfig['refreshTokenExpireAtTimeStamp']);

        $this->assertSame($expectedData['access_token'], $appConfig['accessToken']);
        $this->assertSame($expectedData['refresh_token'], $appConfig['refreshToken']);
        $this->assertSame($expectedData['expires_in'] + $now->getTimestamp(), $appConfig['accessTokenExpireAtTimeStamp']);
        $this->assertSame($expectedData['refresh_expires_in'] + $now->getTimestamp(), $appConfig['refreshTokenExpireAtTimeStamp']);

        $returnedAppConfig = $returnedApp->getConfig();
        $this->assertNotSame($oldConfig['accessToken'], $returnedAppConfig['accessToken']);
        $this->assertNotSame($oldConfig['refreshToken'], $returnedAppConfig['refreshToken']);
        $this->assertNotSame($oldConfig['accessTokenExpireAtTimeStamp'], $returnedAppConfig['accessTokenExpireAtTimeStamp']);
        $this->assertNotSame($oldConfig['refreshTokenExpireAtTimeStamp'], $returnedAppConfig['refreshTokenExpireAtTimeStamp']);

        $this->assertSame($expectedData['access_token'], $returnedAppConfig['accessToken']);
        $this->assertSame($expectedData['refresh_token'], $returnedAppConfig['refreshToken']);
        $this->assertSame($expectedData['expires_in'] + $now->getTimestamp(), $returnedAppConfig['accessTokenExpireAtTimeStamp']);
        $this->assertSame($expectedData['refresh_expires_in'] + $now->getTimestamp(), $returnedAppConfig['refreshTokenExpireAtTimeStamp']);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testRefreshAccessTokenWithNonZeroCodeResponseThrowException(): void
    {
        $this->expectException(AliexpressAccessTokenManagerException::class);

        $now = new DateTime();
        $oldConfig = [
            'seller_id' => Generator::digitsOnlyString(),
            'accessToken' => Generator::string(),
            'refreshToken' => Generator::string(),
            'accessTokenExpireAtTimeStamp' => $now->getTimestamp() + 10,
            'refreshTokenExpireAtTimeStamp' => $now->getTimestamp() + 3600,
        ];
        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);

        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);
        $app->setConfig(array_merge($app->getConfig(), $oldConfig));
        $tenant->populateApp($app);
        $this->tenantRepository->save($tenant);

        $expectedData = [
            'code' => Generator::string(),
            'message' => Generator::string(),
        ];

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->responseMock->method('toArray')->willReturn($expectedData);

        $this->clientMock->method('request')->willReturn($this->responseMock);

        $this->loggerMock->expects($this->once())->method('error');

        $this->accessTokenManager->refreshAccessToken(TenantFactory::TENANT_ID);
    }

    /**
     * @throws TenantIdException
     */
    public function testRefreshAccessTokenWithNon200ResponseThrowException(): void
    {
        $this->expectException(AliexpressAccessTokenManagerException::class);

        $now = new DateTime();
        $oldConfig = [
            'seller_id' => Generator::digitsOnlyString(),
            'accessToken' => Generator::string(),
            'refreshToken' => Generator::string(),
            'accessTokenExpireAtTimeStamp' => $now->getTimestamp() + 10,
            'refreshTokenExpireAtTimeStamp' => $now->getTimestamp() + 3600,
        ];
        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);

        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);
        $app->setConfig(array_merge($app->getConfig(), $oldConfig));
        $tenant->populateApp($app);
        $this->tenantRepository->save($tenant);

        $expectedData = [
            'code' => Generator::string(),
            'message' => Generator::string(),
        ];

        $this->responseMock->method('getStatusCode')->willReturn(400);
        $this->responseMock->method('toArray')->willReturn($expectedData);

        $this->clientMock->method('request')->willReturn($this->responseMock);

        $this->loggerMock->expects($this->once())->method('error');

        $this->accessTokenManager->refreshAccessToken(TenantFactory::TENANT_ID);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testRefreshAccessTokenThrowsAliexpressAccessTokenManagerException(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);
        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);
        $this->assertFalse(isset($app->getConfig()['refreshToken']));

        $this->expectException(AliexpressAccessTokenManagerException::class);
        $this->accessTokenManager->refreshAccessToken(TenantFactory::TENANT_ID);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testGetAccessTokenWorks(): void
    {
        $now = new DateTime();
        $oldConfig = [
            'seller_id' => Generator::digitsOnlyString(),
            'accessToken' => Generator::string(),
            'refreshToken' => Generator::string(),
            'accessTokenExpireAtTimeStamp' => 3600 + $now->getTimestamp(),
            'refreshTokenExpireAtTimeStamp' => 7200 + $now->getTimestamp(),
        ];

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);
        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);
        $app->setConfig(array_merge($app->getConfig(), $oldConfig));
        $tenant->populateApp($app);
        $this->tenantRepository->save($tenant);

        $token = $this->accessTokenManager->getAccessToken(TenantFactory::TENANT_ID);
        $this->assertSame($oldConfig['accessToken'], $token);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testGetAccessTokenGetsNewToken(): void
    {
        $now = new DateTime();
        $oldConfig = [
            'seller_id' => Generator::digitsOnlyString(),
            'accessToken' => Generator::string(),
            'refreshToken' => Generator::string(),
            'accessTokenExpireAtTimeStamp' => -3600 + $now->getTimestamp(),
            'refreshTokenExpireAtTimeStamp' => 7200 + $now->getTimestamp(),
        ];

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);
        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);
        $app->setConfig(array_merge($app->getConfig(), $oldConfig));
        $tenant->populateApp($app);
        $this->tenantRepository->save($tenant);

        $expectedData = [
            'seller_id' => Generator::digitsOnlyString(),
            'access_token' => Generator::string(),
            'refresh_token' => Generator::string(),
            'expires_in' => 3600,
            'refresh_expires_in' => 7200,
        ];

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->responseMock->method('toArray')->willReturn($expectedData);

        $this->clientMock->method('request')->willReturn($this->responseMock);

        $token = $this->accessTokenManager->getAccessToken(TenantFactory::TENANT_ID);
        $this->assertNotSame($oldConfig['accessToken'], $token);
        $this->assertSame($expectedData['access_token'], $token);
    }

    /**
     * @throws TenantIdException
     */
    public function testGetAccessTokenThrowsAliexpressAccessTokenManagerException(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::TENANT_ID);
        $this->assertNotNull($tenant);
        $app = $tenant->getApp($this->appId);
        $this->assertNotNull($app);
        $this->assertFalse(isset($app->getConfig()['accessToken']));

        $this->expectException(AliexpressAccessTokenManagerException::class);
        $this->accessTokenManager->getAccessToken(TenantFactory::TENANT_ID);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     */
    public function testGetAccessTokenWithNonExistTenantThrowsAliexpressAccessTokenManagerException(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById(TenantFactory::NON_EXISTING_TENANT_ID);
        $this->assertNull($tenant);

        $this->expectException(TenantIdException::class);
        $this->accessTokenManager->getAccessToken(TenantFactory::NON_EXISTING_TENANT_ID);
    }
}
