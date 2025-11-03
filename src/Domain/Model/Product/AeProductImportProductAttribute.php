<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use Symfony\Component\Uid\Uuid;

class AeProductImportProductAttribute
{
    private Uuid $id;

    private AeProductImportProduct $aeProductImportProduct;

    private string $aeAttributeType;

    private string $aeAttributeName;

    private string $aeAttributeValue;

    public function __construct(
        AeProductImportProduct $aeProductImportProduct,
        AeAttributeType $aeAttributeType,
        string $aeAttributeName,
        string $aeAttributeValue
    ) {
        $this->id = Uuid::v4();
        $this->aeProductImportProduct = $aeProductImportProduct;
        $this->aeAttributeType = $aeAttributeType->value;
        $this->aeAttributeName = $aeAttributeName;
        $this->aeAttributeValue = $aeAttributeValue;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAeProductImportProduct(): AeProductImportProduct
    {
        return $this->aeProductImportProduct;
    }

    public function getAeAttributeType(): AeAttributeType
    {
        return AeAttributeType::from($this->aeAttributeType);
    }

    public function getAeAttributeName(): string
    {
        return $this->aeAttributeName;
    }

    public function getAeAttributeValue(): string
    {
        return $this->aeAttributeValue;
    }
}
