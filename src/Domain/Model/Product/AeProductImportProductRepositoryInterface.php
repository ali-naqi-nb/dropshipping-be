<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

interface AeProductImportProductRepositoryInterface
{
    public function findOneByAeProductIdAndAeSkuId(int|string $aeProductId, int|string $aeSkuId): ?AeProductImportProduct;

    public function findOneBy(array $criteria): ?AeProductImportProduct;

    public function findByAeProductId(string $aeProductId): array;

    public function findOneByNbProductId(string $nbProductId): ?AeProductImportProduct;

    public function save(AeProductImportProduct $importProduct): void;

    public function delete(AeProductImportProduct $importProduct): void;
}
