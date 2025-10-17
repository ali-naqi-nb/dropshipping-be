<?php

namespace App\Domain\Model\Product;

interface AeProductImportProductAttributeRepositoryInterface
{
    public function findByAeProductId(string $aeProductId): ?array;
}
