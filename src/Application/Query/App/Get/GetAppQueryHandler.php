<?php

declare(strict_types=1);

namespace App\Application\Query\App\Get;

use App\Application\Service\App\AppResponseMapper;
use App\Application\Shared\App\AppResponse;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\AppValidatorInterface;
use App\Domain\Model\Tenant\TenantRepositoryInterface;

final class GetAppQueryHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly AppValidatorInterface $validator,
        private readonly AppResponseMapper $responseMapper
    ) {
    }

    public function __invoke(GetAppQuery $query): AppResponse|ErrorResponse
    {
        $appError = $this->validator->validateAppId($query->getAppId());
        if (null !== $appError) {
            return ErrorResponse::fromConstraintViolation($appError);
        }

        $tenant = $this->tenantRepository->findOneById($query->getTenantId());
        if (null === $tenant) {
            return ErrorResponse::notFound('Tenant not found');
        }

        $appId = AppId::from($query->getAppId());
        $app = $tenant->getApp($appId);
        if (null === $app) {
            return ErrorResponse::notFound('App not found');
        }

        return $this->responseMapper->getResponse($app);
    }
}
