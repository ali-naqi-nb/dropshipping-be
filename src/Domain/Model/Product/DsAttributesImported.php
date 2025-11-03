<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class DsAttributesImported implements DomainEventInterface
{
    public function __construct(
        private readonly string $productTypeId,
        private readonly string $dsProductId,
        private readonly string $dsProvider,
        private readonly array $attributes,
        private readonly string $status,
    ) {
    }

    public function getProductTypeId(): string
    {
        return $this->productTypeId;
    }

    public function getDsProvider(): string
    {
        return $this->dsProvider;
    }

    public function getDsProductId(): string
    {
        return $this->dsProductId;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
