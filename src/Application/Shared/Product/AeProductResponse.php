<?php

declare(strict_types=1);

namespace App\Application\Shared\Product;

use App\Domain\Model\Bus\Command\CommandResponseInterface;
use App\Domain\Model\Bus\Query\QueryResponseInterface;
use App\Domain\Model\Product\AeAttributeType;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductAttribute;

final class AeProductResponse implements QueryResponseInterface, CommandResponseInterface
{
    /**
     * @param AeVariantAttributeResponse[] $aeVariantAttributes
     * @param string[]                     $aeProductImageUrls
     * @param AeShippingOptionResponse[]   $aeProductShippingOptions
     */
    private function __construct(
        private readonly int|string $aeProductId,
        private readonly int|string $aeSkuId,
        private readonly string $aeProductName,
        private readonly ?string $variantName,
        private readonly ?string $aeProductCategoryName,
        private readonly int $aeSkuStock,
        private readonly ?int $aeSkuPrice,
        private readonly ?int $aeOfferSalePrice,
        private readonly ?int $aeOfferBulkSalePrice,
        private readonly ?string $aeSkuPriceCurrency,
        private readonly array $aeVariantAttributes,
        private readonly array $aeProductImageUrls,
        private readonly array $aeProductShippingOptions,
    ) {
    }

    public function getAeProductId(): int|string
    {
        return $this->aeProductId;
    }

    public function getAeSkuId(): int|string
    {
        return $this->aeSkuId;
    }

    public function getAeProductName(): string
    {
        return $this->aeProductName;
    }

    public function getVariantName(): ?string
    {
        return $this->variantName;
    }

    public function getAeProductCategoryName(): ?string
    {
        return $this->aeProductCategoryName;
    }

    public function getAeSkuStock(): int
    {
        return $this->aeSkuStock;
    }

    public function getAeSkuPrice(): ?int
    {
        return $this->aeSkuPrice;
    }

    public function getAeOfferSalePrice(): ?int
    {
        return $this->aeOfferSalePrice;
    }

    public function getAeOfferBulkSalePrice(): ?int
    {
        return $this->aeOfferBulkSalePrice;
    }

    public function getAeSkuPriceCurrency(): ?string
    {
        return $this->aeSkuPriceCurrency;
    }

    /**
     * @return AeVariantAttributeResponse[]
     */
    public function getAeVariantAttributes(): array
    {
        return $this->aeVariantAttributes;
    }

    /**
     * @return string[]
     */
    public function getAeProductImageUrls(): array
    {
        return $this->aeProductImageUrls;
    }

    /**
     * @return AeShippingOptionResponse[]
     */
    public function getAeProductShippingOptions(): array
    {
        return $this->aeProductShippingOptions;
    }

    /**
     * @param AeShippingOptionResponse[] $shippingOptions
     */
    public static function fromAeProduct(
        AeProductImportProduct $importProduct,
        array $shippingOptions,
    ): self {
        $images = $importProduct->getAeProductImageUrls();
        arsort($images);

        /** @var string[] $urls */
        $urls = [];
        foreach ($images as $url => $__) {
            $urls[] = $url;
        }

        $variantName = self::generateVariantName($importProduct);

        return new self(
            aeProductId: $importProduct->getAeProductId(),
            aeSkuId: $importProduct->getAeSkuId(),
            aeProductName: $importProduct->getAeProductName(),
            variantName: $variantName,
            aeProductCategoryName: $importProduct->getAeProductCategoryName(),
            aeSkuStock: $importProduct->getAeProductStock(),
            aeSkuPrice: $importProduct->getAeSkuPrice(),
            aeOfferSalePrice: $importProduct->getAeOfferSalePrice(),
            aeOfferBulkSalePrice: $importProduct->getAeOfferBulkSalePrice(),
            aeSkuPriceCurrency: $importProduct->getAeSkuCurrencyCode(),
            aeVariantAttributes: array_map(
                fn (AeProductImportProductAttribute $aeAttribute) => AeVariantAttributeResponse::fromAeAttribute($aeAttribute),
                $importProduct->getAeVariantAttributes()
            ),
            aeProductImageUrls: $urls,
            aeProductShippingOptions: $shippingOptions,
        );
    }

    private static function generateVariantName(AeProductImportProduct $importProduct): ?string
    {
        $variantAttributes = $importProduct->getAeVariantAttributes();

        $variantValues = [];
        foreach ($variantAttributes as $attribute) {
            if (AeAttributeType::SkuProperty === $attribute->getAeAttributeType()) {
                $variantValues[] = $attribute->getAeAttributeValue();
            }
        }

        if (empty($variantValues)) {
            return null;
        }

        return implode(', ', $variantValues);
    }
}
