<?php

declare(strict_types=1);

namespace App\Domain\Model\Log;

interface MainLogRepositoryInterface
{
    /**
     * Save a log entry to the main database.
     */
    public function save(MainLog $log): void;

    /**
     * Find a log entry by ID.
     */
    public function findOneById(int $id): ?MainLog;

    /**
     * Find log entries by level.
     *
     * @return MainLog[]
     */
    public function findByLevel(string $level, ?int $limit = null): array;

    /**
     * Find log entries by tenant ID.
     *
     * @return MainLog[]
     */
    public function findByTenantId(string $tenantId, ?int $limit = null): array;

    /**
     * Find log entries by channel.
     *
     * @return MainLog[]
     */
    public function findByChannel(string $channel, ?int $limit = null): array;

    /**
     * Delete log entries older than a given date.
     */
    public function deleteOlderThan(\DateTime $date): int;
}
