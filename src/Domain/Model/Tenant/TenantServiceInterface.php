<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

interface TenantServiceInterface
{
    public const CHUNK_SIZE = 1000;

    public const CONCURRENCY = 30;

    /**
     * @return Tenant[]
     */
    public function getAll(int $chunk, int $chunkSize = self::CHUNK_SIZE): array;

    /**
     * @return Tenant[]
     */
    public function getTenantsByStatus(int $chunk, array $status = [], int $chunkSize = self::CHUNK_SIZE): array;

    public function create(DropshippingDbCreated $event): void;

    public function update(TenantConfigUpdated $event): void;

    public function executeDbMigrations(string $tenantId): bool;

    /**
     * @param Tenant[] $tenants
     */
    public function executeParallelDbMigrations(array $tenants, int $concurrency = self::CONCURRENCY): void;

    public function isAvailable(string $tenantId): bool;

    public function getDbConfig(string $tenantId): ?DbConfig;

    public function getCompanyId(string $tenantId): ?string;

    public function removeTenant(TenantDeleted $event): void;

    public function removeTenantById(Tenant $tenant): void;

    public function updateStatus(TenantStatusUpdated $event): void;

    public function getAllWithNullDbConfiguredAt(int $chunk, int $chunkSize = self::CHUNK_SIZE): array;
}
