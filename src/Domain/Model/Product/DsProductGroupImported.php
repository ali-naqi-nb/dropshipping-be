<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class DsProductGroupImported implements DomainEventInterface
{
    /**
     * @param array<int, array{dsVariantId: string, productId: string, name:string}> $products
     */
    public function __construct(
        private readonly string $dsProductId,
        private readonly string $dsProvider,
        private readonly array $products,
    ) {
    }

    public function getDsProductId(): string
    {
        return $this->dsProductId;
    }

    public function getDsProvider(): string
    {
        return $this->dsProvider;
    }

    /**
     * @return array<int, array{dsVariantId: string, productId: string, name:string}>
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
