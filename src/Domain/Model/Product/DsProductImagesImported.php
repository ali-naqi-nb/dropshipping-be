<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class DsProductImagesImported implements DomainEventInterface
{
    /**
     * @param array<int, array{dsVariantId: int|string, images: array<array{id: string, originalFilename: string, extension: string, mimeType: string, size: int, width: int, height: int, altText: string}>}> $products
     */
    public function __construct(
        private readonly int|string $dsProductId,
        private readonly string $dsProvider,
        private readonly array $products
    ) {
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
     * @return array<int, array{dsVariantId: int|string, images: array<array{id: string, originalFilename: string, extension: string, mimeType: string, size: int, width: int, height: int, altText: string}>}>
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
