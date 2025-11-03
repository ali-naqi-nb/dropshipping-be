<?php

declare(strict_types=1);

namespace App\Domain\Model\Log;

interface TenantLogRepositoryInterface
{
    /**
     * Save a log entry to the tenant database.
     */
    public function save(TenantLog $log): void;

    /**
     * Find a log entry by ID.
     */
    public function findOneById(int $id): ?TenantLog;

    /**
     * Find log entries by level.
     *
     * @return TenantLog[]
     */
    public function findByLevel(string $level, ?int $limit = null): array;

    /**
     * Find log entries by user ID.
     *
     * @return TenantLog[]
     */
    public function findByUserId(string $userId, ?int $limit = null): array;

    /**
     * Find log entries by request ID.
     *
     * @return TenantLog[]
     */
    public function findByRequestId(string $requestId): array;

    /**
     * Find log entries by channel.
     *
     * @return TenantLog[]
     */
    public function findByChannel(string $channel, ?int $limit = null): array;

    /**
     * Delete log entries older than a given date.
     */
    public function deleteOlderThan(\DateTime $date): int;
}
