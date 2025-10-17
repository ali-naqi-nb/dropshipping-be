<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class TenantStatusUpdated implements DomainEventInterface
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $status
    ) {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getStatus(): ShopStatus
    {
        return ShopStatus::from($this->status);
    }
}
