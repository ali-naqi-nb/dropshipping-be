<?php

declare(strict_types=1);

namespace App\Application\Query\Order\GetBySource;

use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Order\DsOrderMapping;
use App\Domain\Model\Order\DsOrderMappingRepositoryInterface;
use App\Domain\Model\Order\DsProvider;
use App\Domain\Model\Tenant\TenantRepositoryInterface;

final class GetOrdersBySourceQueryHandler
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly DsOrderMappingRepositoryInterface $dsOrderMappingRepository
    ) {
    }

    public function __invoke(GetOrdersBySourceQuery $query): ErrorResponse|array
    {
        $tenant = $this->tenantRepository->findOneById($query->getTenantId());
        if (null === $tenant) {
            return ErrorResponse::notFound('Tenant not found');
        }

        $source = $query->getSource();
        if (false === in_array($source, DsProvider::values())) {
            return ErrorResponse::notFound('Provider not found');
        }

        $dsOrders = $this->dsOrderMappingRepository->findOneByDsProvider($source);

        return array_map(fn (DsOrderMapping $dsOrder) => GetOrdersBySourceResponse::fromDsOrder($dsOrder), $dsOrders);
    }
}
