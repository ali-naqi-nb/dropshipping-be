<?php

declare(strict_types=1);

namespace App\Domain\Model\Order;

interface DsOrderMappingRepositoryInterface
{
    public function findNextId(string $id = null): string;

    public function findOneById(string $id): ?DsOrderMapping;

    public function findOneByDsOrderId(string|int $dsOrderId): ?DsOrderMapping;

    /**
     * @return DsOrderMapping[]
     */
    public function findByNBOrderId(string $nbOrderId): array;

    /**
     * @return DsOrderMapping[]
     */
    public function findOneByDsProvider(string $dsProvider): array;

    public function save(DsOrderMapping $dsOrderMapping): void;

    public function delete(DsOrderMapping $dsOrderMapping): void;
}
