<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class ServiceDbConfigured implements DomainEventInterface
{
    public function __construct(private readonly string $tenantId, private readonly string $serviceName)
    {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }
}
