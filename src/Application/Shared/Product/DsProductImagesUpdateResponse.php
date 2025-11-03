<?php

declare(strict_types=1);

namespace App\Application\Shared\Product;

class DsProductImagesUpdateResponse
{
    public function __construct(
        private readonly string $dsProductId,
        private readonly string $dsProvider,
        private readonly array $products,
        private readonly string $status,
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
     * @return array<array{
     *     productId: string,
     *     images: array<array{
     *         id: string,
     *         originalFilename: string,
     *         extension: string,
     *         mimeType: string,
     *         size: int,
     *         width: int,
     *         height: int,
     *         altText: string
     *     }>
     * }>
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
