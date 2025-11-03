<?php

declare(strict_types=1);

namespace App\Application\Command\Product\SyncAliExpressProducts;

/**
 * Result object for the daily AliExpress product sync operation across all tenants.
 */
final class SyncAliExpressProductsResult
{
    private int $successfulTenants = 0;
    private int $failedTenants = 0;
    private int $skippedTenants = 0;
    private int $successfulProducts = 0;
    private int $failedProducts = 0;
    private int $variantsUpdated = 0;
    private int $variantsSkipped = 0;
    private int $variantsWithErrors = 0;

    public function incrementSuccessfulTenants(): void
    {
        ++$this->successfulTenants;
    }

    public function incrementFailedTenants(): void
    {
        ++$this->failedTenants;
    }

    public function incrementSkippedTenants(): void
    {
        ++$this->skippedTenants;
    }

    public function incrementSuccessfulProducts(): void
    {
        ++$this->successfulProducts;
    }

    public function incrementFailedProducts(): void
    {
        ++$this->failedProducts;
    }

    public function incrementVariantsUpdated(): void
    {
        ++$this->variantsUpdated;
    }

    public function incrementVariantsSkipped(): void
    {
        ++$this->variantsSkipped;
    }

    public function incrementVariantsWithErrors(): void
    {
        ++$this->variantsWithErrors;
    }

    public function getSuccessfulTenants(): int
    {
        return $this->successfulTenants;
    }

    public function getFailedTenants(): int
    {
        return $this->failedTenants;
    }

    public function getSkippedTenants(): int
    {
        return $this->skippedTenants;
    }

    public function getSuccessfulProducts(): int
    {
        return $this->successfulProducts;
    }

    public function getFailedProducts(): int
    {
        return $this->failedProducts;
    }

    public function getVariantsUpdated(): int
    {
        return $this->variantsUpdated;
    }

    public function getVariantsSkipped(): int
    {
        return $this->variantsSkipped;
    }

    public function getVariantsWithErrors(): int
    {
        return $this->variantsWithErrors;
    }

    public function toArray(): array
    {
        return [
            'totalTenantsProcessed' => $this->getTotalTenantsProcessed(),
            'successfulTenants' => $this->successfulTenants,
            'failedTenants' => $this->failedTenants,
            'skippedTenants' => $this->skippedTenants,
            'totalProductsProcessed' => $this->getTotalProductsProcessed(),
            'successfulProducts' => $this->successfulProducts,
            'failedProducts' => $this->failedProducts,
            'variantsUpdated' => $this->variantsUpdated,
            'variantsSkipped' => $this->variantsSkipped,
            'variantsWithErrors' => $this->variantsWithErrors,
        ];
    }

    public function getTotalTenantsProcessed(): int
    {
        return $this->successfulTenants + $this->failedTenants + $this->skippedTenants;
    }

    public function getTotalProductsProcessed(): int
    {
        return $this->successfulProducts + $this->failedProducts;
    }
}
