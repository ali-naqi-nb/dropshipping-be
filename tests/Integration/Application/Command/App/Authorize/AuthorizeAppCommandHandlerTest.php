<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Command\App\Authorize;

use App\Application\Command\App\Authorize\AuthorizeAppCommand;
use App\Application\Command\App\Authorize\AuthorizeAppCommandHandler;
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

final class AuthorizeAppCommandHandlerTest extends IntegrationTestCase
{
    private const REQUIRED_DATA = [
        'tenantId' => TenantFactory::TENANT_ID,
        'appId' => AppFactory::ALI_EXPRESS_ID,
        'token' => AppFactory::ALI_EXPRESS_TOKEN,
    ];

    private AuthorizeAppCommandHandler $handler;
    private TenantRepositoryInterface $tenantRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AuthorizeAppCommandHandler $handler */
        $handler = self::getContainer()->get(AuthorizeAppCommandHandler::class);
        $this->handler = $handler;

        /** @var TenantRepositoryInterface $repository */
        $repository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->tenantRepository = $repository;
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     */
    public function testAuthorizeAppWorksCorrectlyReturnsNull(): void
    {
        $command = new AuthorizeAppCommand(...self::REQUIRED_DATA);

        /** @var Tenant $tenant */
        $tenant = $this->tenantRepository->findOneById($command->getTenantId());
        $this->assertNotNull($tenant);
        $this->assertTrue($tenant->isAppInstalled(AppId::from($command->getAppId())));
        $app = $tenant->getApp(AppId::from($command->getAppId()));
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertArrayNotHasKey('accessToken', $appConfig);
        $this->assertArrayNotHasKey('refreshToken', $appConfig);
        $this->assertArrayNotHasKey('accessTokenExpireAtTimeStamp', $appConfig);
        $this->assertArrayNotHasKey('refreshTokenExpireAtTimeStamp', $appConfig);

        $response = $this->handler->__invoke($command);
        $this->assertNotNull($response);
        $this->assertInstanceOf(AppResponse::class, $response);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->refresh($tenant);
        $app = $tenant->getApp(AppId::from($command->getAppId()));
        $this->assertNotNull($app);

        $appConfig = $app->getConfig();
        $this->assertArrayHasKey('accessToken', $appConfig);
        $this->assertArrayHasKey('refreshToken', $appConfig);
        $this->assertArrayHasKey('accessTokenExpireAtTimeStamp', $appConfig);
        $this->assertArrayHasKey('refreshTokenExpireAtTimeStamp', $appConfig);
        $this->assertArrayHasKey('sellerId', $appConfig);

        $this->assertSame($response->getConfig()['accessToken'], $appConfig['accessToken']);
        $this->assertSame($response->getConfig()['refreshToken'], $appConfig['refreshToken']);
        $this->assertSame($response->getConfig()['accessTokenExpireAtTimeStamp'], $appConfig['accessTokenExpireAtTimeStamp']);
        $this->assertSame($response->getConfig()['refreshTokenExpireAtTimeStamp'], $appConfig['refreshTokenExpireAtTimeStamp']);
        $this->assertSame($response->getConfig()['sellerId'], $appConfig['sellerId']);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testAuthorizeAppWithNonExistTenantReturnsErrorResponse(): void
    {
        $command = new AuthorizeAppCommand(
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
    public function testAuthorizeAppWithNotSupportedAppReturnErrorResponse(): void
    {
        $command = new AuthorizeAppCommand(
            ...array_merge(self::REQUIRED_DATA, ['appId' => AppFactory::APP_ID_NOT_SUPPORTED])
        );
        /** @var ErrorResponse $response */
        $response = $this->handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::Error, $response->getType());
        $this->assertSame(['appId' => 'App "'.$command->getAppId().'" is not supported.'], $response->getErrors());
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testAuthorizeAppWithNonTokenExchangeAppReturnErrorResponse(): void
    {
        $command = new AuthorizeAppCommand(
            ...array_merge(self::REQUIRED_DATA, ['appId' => AppFactory::APP_ID_NOT_SUPPORTED])
        );

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);
        /** @var SymfonyValidatorInterface $symfonyValidatorMock */
        $symfonyValidatorMock = $this->createMock(SymfonyValidatorInterface::class);
        /** @var AppValidatorInterface&MockObject $validatorMock */
        $validatorMock = $this->getMockBuilder(AppValidator::class)
            ->setConstructorArgs([$this->tenantRepository, $translator, $symfonyValidatorMock])
            ->onlyMethods(['validateAppId'])
            ->getMock();
        $validatorMock->method('validateAppId')->willReturn(null);

        /** @var AliexpressAccessTokenManager $aliexpressAccessTokenManager */
        $aliexpressAccessTokenManager = self::getContainer()->get(AliexpressAccessTokenManager::class);

        $responseMapperMock = $this->createMock(AppResponseMapper::class);

        $handler = new AuthorizeAppCommandHandler(
            $validatorMock,
            $this->tenantRepository,
            $translator,
            $aliexpressAccessTokenManager,
            $responseMapperMock
        );

        /** @var ErrorResponse $response */
        $response = $handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::Error, $response->getType());
        $this->assertSame(['appId' => 'App "'.$command->getAppId().'" is not supported for exchange-token.'], $response->getErrors());
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    public function testAuthorizeAppWithNonTokenExchangeAppReturnCommonError(): void
    {
        $command = new AuthorizeAppCommand(
            ...array_merge(self::REQUIRED_DATA, ['appId' => AppFactory::ALI_EXPRESS_ID])
        );

        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);
        /** @var SymfonyValidatorInterface $symfonyValidatorMock */
        $symfonyValidatorMock = $this->createMock(SymfonyValidatorInterface::class);
        /** @var AppValidatorInterface&MockObject $validatorMock */
        $validatorMock = $this->createMock(AppValidator::class);
        $validatorMock->method('validateAppId')->willReturn(null);

        $aliexpressAccessTokenManagerMock = $this->createMock(AliexpressAccessTokenManager::class);
        $aliexpressAccessTokenManagerMock->method('exchangeTemporaryTokenWithAccessToken')->willThrowException(new AliexpressAccessTokenManagerException('Aliexpress service error'));

        $responseMapperMock = $this->createMock(AppResponseMapper::class);

        $handler = new AuthorizeAppCommandHandler(
            $validatorMock,
            $this->tenantRepository,
            $translator,
            $aliexpressAccessTokenManagerMock,
            $responseMapperMock
        );

        /** @var ErrorResponse $response */
        $response = $handler->__invoke($command);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(ErrorType::Error, $response->getType());
    }

    public function testAuthorizeAppOnManagerErrorReturnsErrorResponse(): void
    {
        $command = new AuthorizeAppCommand(...self::REQUIRED_DATA);

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

        $aliexpressAccessTokenManagerExceptionMessage = 'Aliexpress service error';

        $aliexpressAccessTokenManagerMock = $this->createMock(AliexpressAccessTokenManager::class);
        $aliexpressAccessTokenManagerMock->method('exchangeTemporaryTokenWithAccessToken')->willThrowException(new AliexpressAccessTokenManagerException($aliexpressAccessTokenManagerExceptionMessage));

        $responseMapperMock = $this->createMock(AppResponseMapper::class);

        $handler = new AuthorizeAppCommandHandler(
            $validatorMock,
            $this->tenantRepository,
            $translator,
            $aliexpressAccessTokenManagerMock,
            $responseMapperMock
        );

        /** @var ErrorResponse $response */
        $response = $handler->__invoke($command);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(['common' => $aliexpressAccessTokenManagerExceptionMessage], $response->getErrors());
    }
}
