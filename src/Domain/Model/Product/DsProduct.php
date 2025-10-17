<?php

namespace App\Domain\Model\Product;

class DsProduct
{
    private string $productId;

    private int $stock;

    private int $cost;
    private string $currencyCode;

    public function __construct(
        string $productId,
        int $stock,
        int $cost,
        string $currencyCode,
    ) {
        $this->productId = $productId;
        $this->stock = $stock;
        $this->cost = $cost;
        $this->currencyCode = $currencyCode;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}
