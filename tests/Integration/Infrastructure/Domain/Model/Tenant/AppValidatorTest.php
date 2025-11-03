<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Tenant;

use App\Application\Service\TranslatorInterface;
use App\Domain\Model\Error\ConstraintViolation;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Domain\Model\Tenant\AppValidator;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Trait\Assertions\ValidationAssertionsTrait;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class AppValidatorTest extends IntegrationTestCase
{
    use ValidationAssertionsTrait;

    private AppValidator $validator;
    private TenantRepositoryInterface $tenantRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AppValidator $validator */
        $validator = self::getContainer()->get(AppValidator::class);
        $this->validator = $validator;

        /** @var TenantRepositoryInterface $tenantRepository */
        $tenantRepository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * @dataProvider provideValidApps
     */
    public function testIsAppSupportedWithSupportedAppReturnsNull(string $appId): void
    {
        $this->assertNull($this->validator->validateAppId($appId));
    }

    public function testIsAppSupportedWithNotSupportedAppReturnsConstraintViolation(): void
    {
        $result = $this->validator->validateAppId(AppFactory::APP_ID_NOT_SUPPORTED);

        $this->assertInstanceOf(ConstraintViolation::class, $result);
        $this->assertSame('App "not-supported-app-id" is not supported.', $result->getMessage());
        $this->assertSame('appId', $result->getPath());
    }

    /**
     * @dataProvider provideInvalidAppData
     */
    public function testValidateWithInvalidAppData(array $data, array $expectedErrors): void
    {
        $this->assertErrors($expectedErrors, $this->validator->validate($data));
    }

    /** @dataProvider provideExchangeTokenAppId */
    public function testValidateExchangeTokenAppId(string $appId, bool $isValid, ?string $expectedError = null): void
    {
        $result = $this->validator->validateExchangeTokenAppId($appId);
        if ($isValid) {
            $this->assertNull($result);
        } else {
            $this->assertNotNull($result);
            $this->assertSame($expectedError, $result->getMessage());
            $this->assertSame('appId', $result->getPath());
        }
    }

    public function testValidateExchangeTokenAppIdWithNonExistingAppInExchangeTokenAppIdsList(): void
    {
        $appId = AppFactory::APP_ID_NOT_SUPPORTED;
        $tenantRepositoryMock = $this->createMock(TenantRepositoryInterface::class);
        $translator = self::getContainer()->get(TranslatorInterface::class);
        $symfonyValidatorMock = $this->createMock(SymfonyValidatorInterface::class);
        $validatorMock = $this->getMockBuilder(AppValidator::class)
            ->setConstructorArgs([$tenantRepositoryMock, $translator, $symfonyValidatorMock])
            ->onlyMethods(['validateAppId']) // Mock only the 'createUser' method
            ->getMock();
        $validatorMock->method('validateAppId')->willReturn(null);
        $result = $validatorMock->validateExchangeTokenAppId($appId);

        $this->assertSame('App "'.$appId.'" is not supported for exchange-token.', $result->getMessage());
        $this->assertSame('appId', $result->getPath());
    }

    public function testValidateAppInstalled(): void
    {
        $tenantId = TenantFactory::TENANT_FOR_DELETE_ID;
        $appId = AppId::from(AppFactory::ALI_EXPRESS_ID);

        $tenant = $this->tenantRepository->findOneById($tenantId);
        $this->assertNotNull($tenant);

        $this->assertFalse($tenant->isAppInstalled($appId));
        $result = $this->validator->validateAppInstalledAndActive($tenantId, $appId->value);
        $this->assertNotNull($result);
        $this->assertSame('App "'.$appId->value.'" is either not installed or not active', $result->getMessage());
        $this->assertSame('appId', $result->getPath());

        $tenant->installApp(AppId::AliExpress);
        $app = $tenant->getApp($appId);
        $this->assertNotNull($app);
        $app->setConfig(array_merge($app->getConfig(), ['isActive' => true]));
        $tenant->populateApp($app);
        $this->tenantRepository->save($tenant);
        $this->assertTrue($tenant->isAppInstalled($appId));
        $this->assertNull($this->validator->validateAppInstalledAndActive($tenantId, $appId->value));
    }

    public function provideInvalidAppData(): array
    {
        return [
            'missingRequiredData' => [
                [],
                [
                    ['path' => 'config', 'message' => 'This field is missing.'],
                ],
            ],
            'missingIsActive' => [
                [
                    'config' => [
                        'additionalField' => 'value',
                    ],
                ],
                [
                    ['path' => 'config.isActive', 'message' => 'This field is missing.'],
                ],
            ],
            'isActiveWrongDataType' => [
                [
                    'config' => [
                        'isActive' => 1,
                    ],
                ],
                [
                    ['path' => 'config.isActive', 'message' => 'This value should be of type boolean.'],
                ],
            ],
        ];
    }

    public function provideValidApps(): array
    {
        return [
            'ali-express' => [AppFactory::ALI_EXPRESS_ID],
        ];
    }

    public function provideExchangeTokenAppId(): array
    {
        return [
            'valid' => [
                AppFactory::ALI_EXPRESS_ID,
                true,
            ],
            'invalid' => [
                AppFactory::APP_ID_NOT_SUPPORTED,
                false,
                'App "'.AppFactory::APP_ID_NOT_SUPPORTED.'" is not supported.',
            ],
        ];
    }
}
