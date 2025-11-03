<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Domain\Model\Bus\Event\DomainEventInterface;

class UpdateDsProduct implements DomainEventInterface
{
    private string $tenantId;

    private string $dsProvider;

    private DsProduct $product;

    public function __construct(
        string    $tenantId,
        string    $dsProvider,
        DsProduct $product,
    )
    {
        $this->product = $product;
        $this->tenantId = $tenantId;
        $this->dsProvider = $dsProvider;
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
}
