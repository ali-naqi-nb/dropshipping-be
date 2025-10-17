<?php

declare(strict_types=1);

namespace App\Application\Command\Order\AliexpressSyncOrderStatus;

use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Order\DsOrderMappingRepositoryInterface;
use App\Domain\Model\Order\DsOrderStatusUpdated;
use App\Domain\Model\Order\ProcessingStatus;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Domain\Model\Tenant\TenantService;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;

final class AliexpressSyncOrdersStatusCommandHandler
{
    public function __construct(
        private readonly DsOrderMappingRepositoryInterface $dsOrderMappingRepository,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TenantStorageInterface $tenantStorage,
        private readonly TenantService $tenantService,
        private readonly EventBusInterface $eventBus,
        private readonly DoctrineTenantConnection $doctrineTenantConnection,
        private readonly bool $wrapInTransaction,
    ) {
    }

    public function __invoke(AliexpressSyncOrdersStatusCommand $command): ?ErrorResponse
    {
        $tenant = $this->tenantRepository->findOneByAliexpressSellerId($command->getSellerId());

        if (null === $tenant) {
            return ErrorResponse::notFound('Seller does not exist on platform.');
        }

        $response = $this->createDoctrineTenantConnection($tenant->getId());
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        $dsOrderMappingCurrentDsOrder = $this->dsOrderMappingRepository->findOneByDsOrderId($command->getData()['orderId']);
        if (null === $dsOrderMappingCurrentDsOrder) {
            return ErrorResponse::notFound('DSOrder not found.');
        }

        $dsOrderMappingCurrentDsOrder->setDsStatus($command->getData()['orderStatus']);
        $this->dsOrderMappingRepository->save($dsOrderMappingCurrentDsOrder);

        $this->dispatchOrderStatusUpdated($dsOrderMappingCurrentDsOrder->getNbOrderId());

        return null;
    }

    private function dispatchOrderStatusUpdated(string $nbOrderId): void
    {
        $nbOrderStatus = $this->getOrderStatus($nbOrderId);

        if (null !== $nbOrderStatus) {
            $this->eventBus->publish(new DsOrderStatusUpdated(
                nbOrderId: $nbOrderId,
                nbOrderStatus: $nbOrderStatus
            ));
        }
    }

    private function getOrderStatus(string $nbOrderId): ?ProcessingStatus
    {
        $statuses = [
            ProcessingStatus::New->value => 0,
            ProcessingStatus::Processing->value => 0,
            ProcessingStatus::Shipped->value => 0,
            ProcessingStatus::Delivered->value => 0,
            ProcessingStatus::Canceled->value => 0,
        ];
        $status = null;

        $dsOrderMappingWithSameNbOrderId = $this->dsOrderMappingRepository->findByNBOrderId($nbOrderId);
        foreach ($dsOrderMappingWithSameNbOrderId as $dsOrderMapping) {
            $dsOrderMappingStatus = $dsOrderMapping->getDsStatus();

            $nbProcessingStatus = ProcessingStatus::getAeMappingOrderStatus($dsOrderMappingStatus ?? '');
            if (null === $nbProcessingStatus) {
                continue;
            }
            $statuses[$nbProcessingStatus->value] = $statuses[$nbProcessingStatus->value] + 1;
        }

        if ($statuses[ProcessingStatus::New->value] > 0) {
            $status = ProcessingStatus::New;
        } elseif ($statuses[ProcessingStatus::Processing->value] > 0) {
            $status = ProcessingStatus::Processing;
        } elseif ($statuses[ProcessingStatus::Shipped->value] > 0) {
            $status = ProcessingStatus::Shipped;
        } elseif ($statuses[ProcessingStatus::Delivered->value] > 0) {
            $status = ProcessingStatus::Delivered;
        } elseif ($statuses[ProcessingStatus::Canceled->value] > 0) {
            $status = ProcessingStatus::Canceled;
        }

        return $status;
    }

    private function createDoctrineTenantConnection(string $tenantId): ?ErrorResponse
    {
        $this->tenantStorage->setId($tenantId);

        if (!$this->tenantService->isAvailable($tenantId)) {
            return ErrorResponse::fromCommonError(sprintf('Service is unavailable.'));
        }

        $dbConfig = $this->tenantService->getDbConfig($tenantId);
        if (null !== $dbConfig) {
            if (!$this->doctrineTenantConnection->isConnected() && $tenantId !== $this->doctrineTenantConnection->getTenantId()) {
                $this->doctrineTenantConnection->create($dbConfig);
            }

            if ($this->wrapInTransaction) {
                $this->doctrineTenantConnection->beginTransaction();
            }
        }

        return null;
    }
}
