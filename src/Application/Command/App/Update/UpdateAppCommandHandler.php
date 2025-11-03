<?php

declare(strict_types=1);

namespace App\Application\Command\App\Update;

use App\Application\Service\App\AppResponseMapper;
use App\Application\Service\TranslatorInterface;
use App\Application\Shared\App\AppResponse;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\AppValidatorInterface;
use App\Domain\Model\Tenant\TenantRepositoryInterface;

final class UpdateAppCommandHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly AppValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
        private readonly AppResponseMapper $responseMapper
    ) {
    }

    public function __invoke(UpdateAppCommand $command): ErrorResponse|AppResponse
    {
        $errors = $this->validator->validate($command->toArray());
        if ($errors->hasErrors()) {
            return ErrorResponse::fromConstraintViolationList($errors);
        }

        $appError = $this->validator->validateAppId($command->getAppId());
        if (null !== $appError) {
            return ErrorResponse::fromConstraintViolation($appError);
        }

        $tenant = $this->tenantRepository->findOneById($command->getTenantId());
        if (null === $tenant) {
            return ErrorResponse::notFound($this->translator->trans('Tenant not found'));
        }

        $appId = AppId::from($command->getAppId());
        $app = $tenant->getApp($appId);
        if (null === $app) {
            return ErrorResponse::notFound($this->translator->trans('App not found'));
        }

        $app->setConfig(array_merge($app->getConfig(), ['isActive' => $command->getConfig()['isActive']]));
        $tenant->populateApp($app);
        $this->tenantRepository->save($tenant);

        return $this->responseMapper->getResponse($app);
    }
}
