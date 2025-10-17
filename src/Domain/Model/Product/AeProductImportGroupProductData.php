<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

final class AeProductImportGroupProductData
{
    private int $aeProductId;
    private int $aeSkuId;
    private string $name;
    private string $description;
    private string $sku;
    private int $price;
    private string $mainCategoryId;
    private array $additionalCategories;
    private int $stock;
    private string $barcode;
    private int $weight;
    private int $length;
    private int $width;
    private int $height;
    private int $costPerItem;
    private string $productTypeName;
    private array $attributes;
    private array $images;

    public function __construct(
        int $aeProductId,
        int $aeSkuId,
        string $name,
        string $description,
        string $sku,
        int $price,
        string $mainCategoryId,
        array $additionalCategories,
        int $stock,
        string $barcode,
        int $weight,
        int $length,
        int $width,
        int $height,
        int $costPerItem,
        string $productTypeName,
        array $attributes,
        array $images
    ) {
        $this->aeProductId = $aeProductId;
        $this->aeSkuId = $aeSkuId;
        $this->name = $name;
        $this->description = $description;
        $this->sku = $sku;
        $this->price = $price;
        $this->mainCategoryId = $mainCategoryId;
        $this->additionalCategories = $additionalCategories;
        $this->stock = $stock;
        $this->barcode = $barcode;
        $this->weight = $weight;
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->costPerItem = $costPerItem;
        $this->productTypeName = $productTypeName;
        $this->attributes = $attributes;
        $this->images = $images;
    }

    public function getAeProductId(): int
    {
        return $this->aeProductId;
    }

    public function getAeSkuId(): int
    {
        return $this->aeSkuId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getMainCategoryId(): string
    {
        return $this->mainCategoryId;
    }

    public function getAdditionalCategories(): array
    {
        return $this->additionalCategories;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getBarcode(): string
    {
        return $this->barcode;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getCostPerItem(): int
    {
        return $this->costPerItem;
    }

    public function getProductTypeName(): string
    {
        return $this->productTypeName;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function setAeProductId(int $aeProductId): void
    {
        $this->aeProductId = $aeProductId;
    }

    public function setAeSkuId(int $aeSkuId): void
    {
        $this->aeSkuId = $aeSkuId;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function setMainCategoryId(string $mainCategoryId): void
    {
        $this->mainCategoryId = $mainCategoryId;
    }

    public function setAdditionalCategories(array $additionalCategories): void
    {
        $this->additionalCategories = $additionalCategories;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function setBarcode(string $barcode): void
    {
        $this->barcode = $barcode;
    }

    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    public function setCostPerItem(int $costPerItem): void
    {
        $this->costPerItem = $costPerItem;
    }

    public function setProductTypeName(string $productTypeName): void
    {
        $this->productTypeName = $productTypeName;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }
}
