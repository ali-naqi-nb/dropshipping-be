<?php

declare(strict_types=1);

namespace App\Application\Command\App\Delete;

use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\AppValidatorInterface;
use App\Domain\Model\Tenant\TenantRepositoryInterface;

final class DeleteAppCommandHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly AppValidatorInterface $validator,
    ) {
    }

    public function __invoke(DeleteAppCommand $command): ?ErrorResponse
    {
        $appError = $this->validator->validateAppId($command->getAppId());
        if (null !== $appError) {
            return ErrorResponse::fromConstraintViolation($appError);
        }

        $tenant = $this->tenantRepository->findOneById($command->getTenantId());
        if (null === $tenant) {
            return ErrorResponse::notFound('Tenant not found');
        }

        $appId = AppId::from($command->getAppId());
        $app = $tenant->getApp($appId);
        if (null === $app) {
            return ErrorResponse::notFound('App not found');
        }

        $tenant->removeApp($app);
        $this->tenantRepository->save($tenant);

        return null;
    }
}
