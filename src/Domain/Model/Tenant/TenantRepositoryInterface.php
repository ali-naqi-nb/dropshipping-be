<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

interface TenantRepositoryInterface
{
    public const CHUNK_SIZE = 1000;

    public function findOneById(string $id): ?Tenant;

    public function findOneByAliexpressSellerId(string|int $id): ?Tenant;

    /**
     * @return Tenant[]
     */
    public function findAll(int $chunk, int $chunkSize = self::CHUNK_SIZE): array;

    /**
     * @return Tenant[]
     */
    public function findTenantsByStatus(int $chunk, array $status = [], int $chunkSize = self::CHUNK_SIZE): array;

    public function findAllWithNullDbConfiguredAt(int $chunk, int $chunkSize = self::CHUNK_SIZE): array;

    public function save(Tenant $tenant): void;

    public function remove(Tenant $tenant): void;

    /**
     * Find all tenants that have a specific app installed and active.
     *
     * @return Tenant[]
     */
    public function findTenantsWithAppInstalled(AppId $appId, int $chunk, int $chunkSize = self::CHUNK_SIZE): array;
}
