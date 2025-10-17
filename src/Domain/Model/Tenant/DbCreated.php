<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

use App\Domain\Model\Bus\Event\DomainEventInterface;

abstract class DbCreated implements DomainEventInterface
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $defaultLanguage,
        private readonly string $companyId,
        private readonly string $domain,
        private readonly string $config,
        private readonly string $status,
    ) {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getConfig(): string
    {
        return $this->config;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function getStatus(): ShopStatus
    {
        return ShopStatus::from($this->status);
    }
}
