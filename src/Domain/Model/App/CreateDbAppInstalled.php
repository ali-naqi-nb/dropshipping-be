<?php

declare(strict_types=1);

namespace App\Domain\Model\App;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class CreateDbAppInstalled implements DomainEventInterface
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $serviceName,
        private readonly string $appId,
    ) {
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }
}
