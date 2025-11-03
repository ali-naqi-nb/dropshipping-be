<?php

declare(strict_types=1);

namespace App\Application\Query\App\GetAll;

use App\Application\Service\App\AppResponseMapper;
use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\TenantRepositoryInterface;

final class GetAllAppsQueryHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly AppResponseMapper $responseMapper
    ) {
    }

    public function __invoke(GetAllAppsQuery $query): ErrorResponse|array
    {
        $tenant = $this->tenantRepository->findOneById($query->getTenantId());
        if (null === $tenant) {
            return ErrorResponse::notFound('Tenant not found');
        }

        $apps = [];
        foreach (AppId::cases() as $supportedAppId) {
            $app = $tenant->getApp($supportedAppId);
            if (null === $app) {
                $app = App::createWithDefaults($supportedAppId);
            }

            $apps[] = $app;
        }

        return $this->responseMapper->getCollectionResponse($apps);
    }
}
