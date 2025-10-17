<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

interface AeProductImportRepositoryInterface
{
    public function findNextId(?string $id = null): string;

    public function findOneById(string $id): ?AeProductImport;

    public function findOneByAeProductId(int|string $aeProductId): ?AeProductImport;

    public function findOneByAeProductIdAndAeSkuId(int|string $aeProductId, int|string $aeSkuId): ?AeProductImport;

    public function save(AeProductImport $import): void;

    public function delete(AeProductImport $import): void;
}
