<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class TenantDeleted implements DomainEventInterface
{
    public function __construct(private readonly string $tenantId)
    {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }
}
