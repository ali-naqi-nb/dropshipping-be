<?php

namespace App\Domain\Model\Product;

use App\Domain\Model\Bus\Event\DomainEventInterface;
use Symfony\Component\Uid\Uuid;

final class DsProductTypeImported implements DomainEventInterface
{
    public const SUCCESS = 'success';
    private Uuid $productTypeId;

    public function __construct(
        string $productTypeId,
        private readonly string $productTypeName,
        private readonly string $dsProductId,
        private readonly string $dsProvider,
    ) {
        $this->productTypeId = Uuid::fromString($productTypeId);
    }

    public function getProductTypeName(): string
    {
        return $this->productTypeName;
    }

    public function getProductTypeId(): string
    {
        return (string) $this->productTypeId;
    }

    public function getDsProductId(): string
    {
        return $this->dsProductId;
    }

    public function getDsProvider(): string
    {
        return $this->dsProvider;
    }

    public function getStatus(): string
    {
        return $this::SUCCESS;
    }
}
