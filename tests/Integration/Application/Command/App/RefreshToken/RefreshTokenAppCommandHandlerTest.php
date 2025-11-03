<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\App\RefreshToken;

use App\Application\Command\App\RefreshToken\RefreshTokenAppCommand;
use App\Application\Command\App\RefreshToken\RefreshTokenAppCommandHandler;
use App\Application\Service\App\AppResponseMapper;
use App\Application\Service\TranslatorInterface;
use App\Application\Shared\App\AppResponse;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Error\ErrorType;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\AppValidatorInterface;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Domain\Model\Tenant\AppValidator;
use App\Infrastructure\Exception\AliexpressAccessTokenManagerException;
use App\Infrastructure\Exception\TenantIdException;
use App\Infrastructure\Service\App\Aliexpress\AliexpressAccessTokenManager;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class RefreshTokenAppCommandHandlerTest extends IntegrationTestCase
{
    private const REQUIRED_DATA = [
        'tenantId' => TenantFactory::TENANT_ID,
        'appId' => AppFactory::ALI_EXPRESS_ID,
    ];

    private RefreshTokenAppCommandHandler $handler;
    private TenantRepositoryInterface $tenantRepository;
    private AliexpressAccessTokenManager $aliexpressAccessTokenManager;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var RefreshTokenAppCommandHandler $handler */
        $handler = self::getContainer()->get(RefreshTokenAppCommandHandler::class);
        $this->handler = $handler;

        /** @var TenantRepositoryInterface $repository */
        $repository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->tenantRepository = $repository;

        /** @var AliexpressAccessTokenManager $aliexpressAccessTokenManager */
        $aliexpressAccessTokenManager = self::getContainer()->get(AliexpressAccessTokenManager::class);
        $this->aliexpressAccessTokenManager = $aliexpressAccessTokenManager;
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     */
    public function testRefreshTokenAppWorksCorrectlyReturnsAppResponse(): void
    {
        $command = new RefreshTokenAppCommand(...self::REQUIRED_DATA);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById($command->getTenantId());
        $this->assertNotNull($tenant);
        $this->assertTrue($tenant->isAppInstalled(AppId::from($command->getAppId())));
        $app = $tenant->getApp(AppId::from($command->getAppId()));
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertArrayNotHasKey('refreshToken', $appConfig);
        $this->assertArrayNotHasKey('refreshTokenExpireAtTimeStamp', $appConfig);

        $this->aliexpressAccessTokenManager->exchangeTemporaryTokenWithAccessToken($command->getTenantId(), '1234567890');
        $entityManager->refresh($tenant);
        $app = $tenant->getApp(AppId::from($command->getAppId()));
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertArrayHasKey('refreshToken', $appConfig);
        $this->assertArrayHasKey('refreshTokenExpireAtTimeStamp', $appConfig);

        $response = $this->handler->__invoke($command);
        $this->assertNotNull($response);
        $this->assertInstanceOf(AppResponse::class, $response);

        $entityManager->refresh($tenant);
        $app = $tenant->getApp(AppId::from($command->getAppId()));
        $this->assertNotNull($app);

        $refreshedAppConfig = $app->getConfig();
        $this->assertArrayHasKey('refreshToken', $refreshedAppConfig);
        $this->assertArrayHasKey('refreshTokenExpireAtTimeStamp', $refreshedAppConfig);

        $this->assertNotSame($appConfig['refreshToken'], $refreshedAppConfig['refreshToken']);
        $this->assertNotSame($appConfig['refreshTokenExpireAtTimeStamp'], $refreshedAppConfig['refreshTokenExpireAtTimeStamp']);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testRefreshTokenAppWithNonExistTenantReturnsErrorResponse(): void
    {
        $command = new RefreshTokenAppCommand(
            ...array_merge(self::REQUIRED_DATA, ['tenantId' => TenantFactory::NON_EXISTING_TENANT_ID])
        );
        $this->assertNull($this->tenantRepository->findOneById($command->getTenantId()));

        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::NotFound, $response->getType());
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testRefreshTokenAppWithNotSupportedAppReturnErrorResponse(): void
    {
        $command = new RefreshTokenAppCommand(
            ...array_merge(self::REQUIRED_DATA, ['appId' => AppFactory::APP_ID_NOT_SUPPORTED])
        );
        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::Error, $response->getType());
        $this->assertSame(['appId' => 'App "'.$command->getAppId().'" is not supported.'], $response->getErrors());
    }

    public function testRefreshTokenAppOnManagerErrorReturnsErrorResponse(): void
    {
        $command = new RefreshTokenAppCommand(...self::REQUIRED_DATA);

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById($command->getTenantId());
        $this->assertNotNull($tenant);
        $this->assertTrue($tenant->isAppInstalled(AppId::from($command->getAppId())));
        $app = $tenant->getApp(AppId::from($command->getAppId()));
        $this->assertNotNull($app);

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);
        /** @var SymfonyValidatorInterface $symfonyValidatorMock */
        $symfonyValidatorMock = $this->createMock(SymfonyValidatorInterface::class);
        /** @var AppValidatorInterface&MockObject $validatorMock */
        $validatorMock = $this->createMock(AppValidator::class);
        $validatorMock->method('validateAppId')->willReturn(null);

        $aliexpressAccessTokenManagerExceptionMock = $this->createMock(AliexpressAccessTokenManagerException::class);

        $aliexpressAccessTokenManagerMock = $this->createMock(AliexpressAccessTokenManager::class);
        $aliexpressAccessTokenManagerMock->method('refreshAccessToken')->willThrowException($aliexpressAccessTokenManagerExceptionMock);

        $responseMapperMock = $this->createMock(AppResponseMapper::class);

        $handler = new RefreshTokenAppCommandHandler(
            $validatorMock,
            $this->tenantRepository,
            $translator,
            $aliexpressAccessTokenManagerMock,
            $responseMapperMock
        );

        /** @var ErrorResponse $response */
        $response = $handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(['common' => 'Failed to refresh access token.'], $response->getErrors());
    }
}
