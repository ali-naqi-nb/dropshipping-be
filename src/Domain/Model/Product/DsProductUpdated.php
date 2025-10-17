<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class DsProductUpdated implements DomainEventInterface
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $dsProvider,
        private readonly DsProduct $product,
    ) {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getDsProvider(): string
    {
        return $this->dsProvider;
    }

    public function getProduct(): DsProduct
    {
        return $this->product;
    }

    public function toArray(): array
    {
        return [
            'tenantId' => $this->getTenantId(),
            'dsProvider' => $this->getDsProvider(),
            'product' => [
                'productId' => $this->getProduct()->getProductId(),
                'stock' => $this->getProduct()->getStock(),
                'cost' => $this->getProduct()->getCost(),
                'currencyCode' => $this->getProduct()->getCurrencyCode(),
            ],
        ];
    }
}
