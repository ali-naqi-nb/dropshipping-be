<?php

declare(strict_types=1);

namespace App\Application\Command\Product\SyncAliExpressProducts;

/**
 * Command to sync stock and supplier prices from AliExpress for all imported products.
 * This command is designed to run daily via scheduled job.
 */
final class SyncAliExpressProductsCommand
{
    public const DEFAULT_CONCURRENCY = 10; // Process 10 tenants in parallel by default
    public const DEFAULT_TIMEOUT_MINUTES = 30; // 30 minutes default timeout per tenant

    public function __construct(
        private readonly bool    $dryRun = false,
        private readonly ?string $tenantId = null,
        private readonly int     $timeoutMinutes = self::DEFAULT_TIMEOUT_MINUTES,
    )
    {
    }

    /**
     * If true, performs validation and logging but does not persist changes.
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * Optional tenant ID to sync only a specific tenant (null = sync all tenants in parallel).
     */
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    /**
     * Timeout in minutes for each tenant sync process (default: 30 minutes).
     */
    public function getTimeoutMinutes(): int
    {
        return $this->timeoutMinutes;
    }

    /**
     * Get timeout in seconds for process execution.
     */
    public function getTimeoutSeconds(): int
    {
        return $this->timeoutMinutes * 60;
    }
}
