<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

class AeProductImportProduct
{
    private int|string $aeProductId;

    private int|string $aeSkuId;

    private string $aeSkuAttr;

    private ?string $aeSkuCode;

    private ?Uuid $nbProductId;

    private string $aeProductName;

    private ?string $aeProductDescription;

    private ?string $aeProductCategoryName;

    private ?string $aeProductBarcode;

    private ?int $aeProductWeight;

    private ?int $aeProductLength;

    private ?int $aeProductWidth;

    private ?int $aeProductHeight;

    private int $aeProductStock = 0;

    private ?int $aeSkuPrice;

    private ?int $aeOfferSalePrice;

    private ?int $aeOfferBulkSalePrice;

    private ?string $aeSkuCurrencyCode;

    private ?string $aeFreightCode;

    private ?int $aeShippingFee;

    private ?string $aeShippingFeeCurrency;

    private ?DateTimeInterface $createdAt = null;

    private ?DateTimeInterface $updatedAt = null;

    private Collection $aeVariantAttributes;

    private Collection $aeProductImageUrls;

    public function __construct(
        int|string $aeProductId,
        int|string $aeSkuId,
        string $aeSkuAttr,
        ?string $aeSkuCode,
        ?string $nbProductId,
        string $aeProductName,
        ?string $aeProductDescription,
        ?string $aeProductCategoryName,
        ?string $aeProductBarcode,
        ?int $aeProductWeight,
        ?int $aeProductLength,
        ?int $aeProductWidth,
        ?int $aeProductHeight,
        int $aeProductStock,
        ?int $aeSkuPrice,
        ?int $aeOfferSalePrice,
        ?int $aeOfferBulkSalePrice,
        ?string $aeSkuCurrencyCode,
        ?string $aeFreightCode,
        ?int $aeShippingFee,
        ?string $aeShippingFeeCurrency
    ) {
        $this->aeProductId = $aeProductId;
        $this->aeSkuId = $aeSkuId;
        $this->aeSkuAttr = $aeSkuAttr;
        $this->aeSkuCode = $aeSkuCode;
        $this->nbProductId = (null !== $nbProductId) ? Uuid::fromString($nbProductId) : null;
        $this->aeProductName = $aeProductName;
        $this->aeProductDescription = $aeProductDescription;
        $this->aeProductCategoryName = $aeProductCategoryName;
        $this->aeProductBarcode = $aeProductBarcode;
        $this->aeProductWeight = $aeProductWeight;
        $this->aeProductLength = $aeProductLength;
        $this->aeProductWidth = $aeProductWidth;
        $this->aeProductHeight = $aeProductHeight;
        $this->aeProductStock = $aeProductStock;
        $this->aeSkuPrice = $aeSkuPrice;
        $this->aeOfferSalePrice = $aeOfferSalePrice;
        $this->aeOfferBulkSalePrice = $aeOfferBulkSalePrice;
        $this->aeSkuCurrencyCode = $aeSkuCurrencyCode;
        $this->aeFreightCode = $aeFreightCode;
        $this->aeShippingFee = $aeShippingFee;
        $this->aeShippingFeeCurrency = $aeShippingFeeCurrency;
        $this->aeVariantAttributes = new ArrayCollection();
        $this->aeProductImageUrls = new ArrayCollection();
    }

    public function setAeProductId(int|string $aeProductId): void
    {
        $this->aeProductId = $aeProductId;
    }

    public function getAeProductId(): int|string
    {
        return $this->aeProductId;
    }

    public function setAeSkuId(int|string $aeSkuId): void
    {
        $this->aeSkuId = $aeSkuId;
    }

    public function getAeSkuId(): int|string
    {
        return $this->aeSkuId;
    }

    public function setAeSkuAttr(string $aeSkuAttr): void
    {
        $this->aeSkuAttr = $aeSkuAttr;
    }

    public function getAeSkuAttr(): string
    {
        return $this->aeSkuAttr;
    }

    public function setAeSkuCode(?string $aeSkuCode): void
    {
        $this->aeSkuCode = $aeSkuCode;
    }

    public function getAeSkuCode(): ?string
    {
        return $this->aeSkuCode;
    }

    public function setNbProductId(?string $nbProductId): void
    {
        $this->nbProductId = (null !== $nbProductId) ? Uuid::fromString($nbProductId) : null;
    }

    public function getNbProductId(): ?string
    {
        return $this->nbProductId?->__toString();
    }

    public function setAeProductName(string $aeProductName): void
    {
        $this->aeProductName = $aeProductName;
    }

    public function getAeProductName(): string
    {
        return $this->aeProductName;
    }

    public function setAeProductDescription(?string $aeProductDescription): void
    {
        $this->aeProductDescription = $aeProductDescription;
    }

    public function getAeProductDescription(): ?string
    {
        return $this->aeProductDescription;
    }

    public function setAeProductCategoryName(?string $aeProductCategoryName): void
    {
        $this->aeProductCategoryName = $aeProductCategoryName;
    }

    public function getAeProductCategoryName(): ?string
    {
        return $this->aeProductCategoryName;
    }

    public function setAeProductBarcode(?string $aeProductBarcode): void
    {
        $this->aeProductBarcode = $aeProductBarcode;
    }

    public function getAeProductBarcode(): ?string
    {
        return $this->aeProductBarcode;
    }

    public function setAeProductWeight(?int $aeProductWeight): void
    {
        $this->aeProductWeight = $aeProductWeight;
    }

    public function getAeProductWeight(): ?int
    {
        return $this->aeProductWeight;
    }

    public function setAeProductLength(?int $aeProductLength): void
    {
        $this->aeProductLength = $aeProductLength;
    }

    public function getAeProductLength(): ?int
    {
        return $this->aeProductLength;
    }

    public function setAeProductWidth(?int $aeProductWidth): void
    {
        $this->aeProductWidth = $aeProductWidth;
    }

    public function getAeProductWidth(): ?int
    {
        return $this->aeProductWidth;
    }

    public function setAeProductHeight(?int $aeProductHeight): void
    {
        $this->aeProductHeight = $aeProductHeight;
    }

    public function getAeProductHeight(): ?int
    {
        return $this->aeProductHeight;
    }

    public function setAeProductStock(int $aeProductStock): void
    {
        $this->aeProductStock = $aeProductStock;
    }

    public function getAeProductStock(): int
    {
        return $this->aeProductStock;
    }

    public function setAeSkuPrice(?int $aeSkuPrice): void
    {
        $this->aeSkuPrice = $aeSkuPrice;
    }

    public function getAeSkuPrice(): ?int
    {
        return $this->aeSkuPrice;
    }

    public function getAeOfferSalePrice(): ?int
    {
        return $this->aeOfferSalePrice;
    }

    public function setAeOfferSalePrice(?int $aeOfferSalePrice): void
    {
        $this->aeOfferSalePrice = $aeOfferSalePrice;
    }

    public function getAeOfferBulkSalePrice(): ?int
    {
        return $this->aeOfferBulkSalePrice;
    }

    public function setAeOfferBulkSalePrice(?int $aeOfferBulkSalePrice): void
    {
        $this->aeOfferBulkSalePrice = $aeOfferBulkSalePrice;
    }

    public function setAeSkuCurrencyCode(?string $aeSkuCurrencyCode): void
    {
        $this->aeSkuCurrencyCode = $aeSkuCurrencyCode;
    }

    public function getAeSkuCurrencyCode(): ?string
    {
        return $this->aeSkuCurrencyCode;
    }

    public function setAeFreightCode(?string $aeFreightCode): void
    {
        $this->aeFreightCode = $aeFreightCode;
    }

    public function getAeFreightCode(): ?string
    {
        return $this->aeFreightCode;
    }

    public function setAeShippingFee(?int $aeShippingFee): void
    {
        $this->aeShippingFee = $aeShippingFee;
    }

    public function getAeShippingFee(): ?int
    {
        return $this->aeShippingFee;
    }

    public function setAeShippingFeeCurrency(?string $aeShippingFeeCurrency): void
    {
        $this->aeShippingFeeCurrency = $aeShippingFeeCurrency;
    }

    public function getAeShippingFeeCurrency(): ?string
    {
        return $this->aeShippingFeeCurrency;
    }

    /**
     * @param AeProductImportProductAttribute[] $aeVariantAttributes
     */
    public function setAeVariantAttributes(array $aeVariantAttributes): void
    {
        $this->aeVariantAttributes->clear();
        foreach ($aeVariantAttributes as $attribute) {
            $this->aeVariantAttributes->add($attribute);
        }
    }

    /**
     * @return AeProductImportProductAttribute[]
     */
    public function getAeVariantAttributes(): array
    {
        return $this->aeVariantAttributes->toArray();
    }

    /**
     * @param array<string, bool> $imageUrls
     */
    public function setAeProductImageUrls(array $imageUrls): void
    {
        $this->aeProductImageUrls->clear();
        foreach ($imageUrls as $imageUrl => $isMain) {
            $this->aeProductImageUrls->add(new AeProductImportProductImage($this, $imageUrl, $isMain));
        }
    }

    /**
     * @return array<string, bool>
     */
    public function getAeProductImageUrls(): array
    {
        /** @var AeProductImportProductImage[] $aeImageUrls */
        $aeImageUrls = $this->aeProductImageUrls->toArray();

        /** @var array<string, bool> $images */
        $images = [];

        foreach ($aeImageUrls as $image) {
            $images[$image->getAeImageUrl()] = $image->isMain();
        }

        return $images;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}
