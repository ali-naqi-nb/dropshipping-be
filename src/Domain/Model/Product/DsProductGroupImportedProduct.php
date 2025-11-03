<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

final class DsProductGroupImportedProduct
{
    public function __construct(
        private readonly string $dsVariantId,
        private readonly string $productId,
        private readonly string $name,
    ) {
    }

    public function getDsVariantId(): string
    {
        return $this->dsVariantId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
