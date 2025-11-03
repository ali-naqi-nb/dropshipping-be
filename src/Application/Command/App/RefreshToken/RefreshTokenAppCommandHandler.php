<?php

declare(strict_types=1);

namespace App\Application\Command\App\RefreshToken;

use App\Application\Service\App\AppResponseMapper;
use App\Application\Service\TranslatorInterface;
use App\Application\Shared\App\AppResponse;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\AppValidatorInterface;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Exception\AliexpressAccessTokenManagerException;
use App\Infrastructure\Exception\TenantIdException;
use App\Infrastructure\Service\App\Aliexpress\AliexpressAccessTokenManager;

final class RefreshTokenAppCommandHandler
{
    public function __construct(
        private readonly AppValidatorInterface $validator,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TranslatorInterface $translator,
        private readonly AliexpressAccessTokenManager $aliexpressAccessTokenManager,
        private readonly AppResponseMapper $responseMapper
    ) {
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     */
    public function __invoke(RefreshTokenAppCommand $command): ErrorResponse|AppResponse
    {
        $tenant = $this->tenantRepository->findOneById($command->getTenantId());
        if (null === $tenant) {
            return ErrorResponse::notFound($this->translator->trans('Tenant not found.'));
        }

        if ($appError = $this->validator->validateExchangeTokenAppId($command->getAppId())) {
            return ErrorResponse::fromConstraintViolation($appError);
        }

        if ($appInstalledError = $this->validator->validateAppInstalledAndActive($command->getTenantId(), $command->getAppId())) {
            return ErrorResponse::fromConstraintViolation($appInstalledError);
        }

        $app = $tenant->getApp(AppId::from($command->getAppId()));
        if (null !== $app && $command->getAppId() === AppId::AliExpress->value) {
            try {
                $updatedApp = $this->aliexpressAccessTokenManager->refreshAccessToken(
                    $command->getTenantId()
                );

                if (null === $updatedApp) {
                    return ErrorResponse::fromCommonError($this->translator->trans('Failed to refresh access token.'));
                }

                return $this->responseMapper->getResponse($updatedApp);
            } catch (AliexpressAccessTokenManagerException $exception) {
                return ErrorResponse::fromCommonError($this->translator->trans($exception->getMessage()));
            }
        }

        return ErrorResponse::notFound();
    }
}
