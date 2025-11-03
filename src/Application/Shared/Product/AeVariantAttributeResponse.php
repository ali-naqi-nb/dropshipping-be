<?php

declare(strict_types=1);

namespace App\Application\Shared\Product;

use App\Domain\Model\Product\AeProductImportProductAttribute;

final class AeVariantAttributeResponse
{
    private function __construct(
        private readonly string $aeVariantAttributeName,
        private readonly string $aeVariantAttributeType,
        private readonly string $aeVariantAttributeValue,
    ) {
    }

    public function getAeVariantAttributeName(): string
    {
        return $this->aeVariantAttributeName;
    }

    public function getAeVariantAttributeType(): string
    {
        return $this->aeVariantAttributeType;
    }

    public function getAeVariantAttributeValue(): string
    {
        return $this->aeVariantAttributeValue;
    }

    public static function fromAeAttribute(AeProductImportProductAttribute $attribute): self
    {
        return new self(
            aeVariantAttributeName: $attribute->getAeAttributeName(),
            aeVariantAttributeType: $attribute->getAeAttributeType()->value,
            aeVariantAttributeValue: $attribute->getAeAttributeValue()
        );
    }
}
