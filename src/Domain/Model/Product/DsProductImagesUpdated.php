<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class DsProductImagesUpdated implements DomainEventInterface
{
    public function __construct(
        private readonly int|string $dsProductId,
        private readonly string $dsProvider,
        private readonly array $products,
        private readonly string $status
    ) {
    }

    public function toArray(): array
    {
        return [
            'dsProductId' => $this->getDsProductId(),
            'dsProvider' => $this->getDsProvider(),
            'products' => $this->getProducts(),
            'status' => $this->getStatus(),
        ];
    }

    public function getDsProductId(): int|string
    {
        return $this->dsProductId;
    }

    public function getDsProvider(): string
    {
        return $this->dsProvider;
    }

    /**
     * @return array<string>
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
