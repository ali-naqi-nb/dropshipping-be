<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use Symfony\Component\Uid\Uuid;

class AeProductImportProductImage
{
    private Uuid $id;

    private AeProductImportProduct $aeProductImportProduct;

    private string $aeImageUrl;

    private bool $isMain;

    public function __construct(
        AeProductImportProduct $aeProductImportProduct,
        string $aeImageUrl,
        bool $isMain,
    ) {
        $this->id = Uuid::v4();
        $this->aeProductImportProduct = $aeProductImportProduct;
        $this->aeImageUrl = $aeImageUrl;
        $this->isMain = $isMain;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAeProductImportProduct(): AeProductImportProduct
    {
        return $this->aeProductImportProduct;
    }

    public function getAeImageUrl(): string
    {
        return $this->aeImageUrl;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }
}
