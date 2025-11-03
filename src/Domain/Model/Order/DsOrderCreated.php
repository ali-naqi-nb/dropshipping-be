<?php

declare(strict_types=1);

namespace App\Domain\Model\Order;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class DsOrderCreated implements DomainEventInterface
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $dsProvider,
        private readonly DsOrderCreatedData $order,
    ) {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getDsProvider(): string
    {
        return $this->dsProvider;
    }

    public function getOrder(): DsOrderCreatedData
    {
        return $this->order;
    }
}
