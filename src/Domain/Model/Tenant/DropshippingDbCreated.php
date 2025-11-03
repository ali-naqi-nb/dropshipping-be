<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

final class DropshippingDbCreated extends DbCreated
{
    private readonly bool $dbCreated;

    public function __construct(
        private readonly string $tenantId,
        private readonly string $defaultCurrency,
        private readonly string $defaultLanguage,
        private readonly string $companyId,
        private readonly string $domain,
        private readonly string $config,
        private readonly string $status,
        bool $dbCreated = false
    ) {
        parent::__construct(
            $this->tenantId,
            $this->defaultLanguage,
            $this->companyId,
            $this->domain,
            $this->config,
            $this->status
        );
        $this->dbCreated = $dbCreated;
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    public function isDbCreated(): bool
    {
        return $this->dbCreated;
    }
}
