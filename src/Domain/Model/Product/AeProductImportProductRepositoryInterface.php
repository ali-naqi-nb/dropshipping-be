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

    /**
     * Get all distinct AliExpress product IDs that have been imported and linked to NextBasket products.
     * Only returns product IDs where nbProductId is not null.
     *
     * @return array<int>
     */
    public function findAllDistinctAeProductIds(?int $limit = null): array;

    /**
     * Find all variants for a given AliExpress product ID.
     *
     * @return array<AeProductImportProduct>
     */
    public function findAllByAeProductId(int|string $aeProductId): array;
}
