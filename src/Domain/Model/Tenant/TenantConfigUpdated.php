<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class TenantConfigUpdated implements DomainEventInterface
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $defaultLanguage,
        private readonly string $defaultCurrency,
    ) {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }
}
