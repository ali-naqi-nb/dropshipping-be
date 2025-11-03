<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\AeProductImportProductAttribute;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;
use DateTime;

final class AeProductImportProductTest extends UnitTestCase
{
    public function testConstructor(): void
    {
        $productImport = Factory::createAeProductImportProduct();
        $this->assertSame(Factory::AE_PRODUCT_ID, $productImport->getAeProductId());
        $this->assertSame(Factory::AE_SKU_ID, $productImport->getAeSkuId());
        $this->assertSame(Factory::AE_SKU_ATTR, $productImport->getAeSkuAttr());
        $this->assertSame(Factory::AE_SKU_CODE, $productImport->getAeSkuCode());
        $this->assertSame(Factory::NB_PRODUCT_ID, $productImport->getNbProductId());
        $this->assertSame(Factory::AE_PRODUCT_NAME, $productImport->getAeProductName());
        $this->assertSame(Factory::AE_PRODUCT_DESCRIPTION, $productImport->getAeProductDescription());
        $this->assertSame(Factory::AE_PRODUCT_CATEGORY_NAME, $productImport->getAeProductCategoryName());
        $this->assertSame(Factory::AE_PRODUCT_BARCODE, $productImport->getAeProductBarcode());
        $this->assertSame(Factory::AE_PRODUCT_WEIGHT, $productImport->getAeProductWeight());
        $this->assertSame(Factory::AE_PRODUCT_LENGTH, $productImport->getAeProductLength());
        $this->assertSame(Factory::AE_PRODUCT_WIDTH, $productImport->getAeProductWidth());
        $this->assertSame(Factory::AE_PRODUCT_HEIGHT, $productImport->getAeProductHeight());
        $this->assertSame(Factory::AE_PRODUCT_STOCK, $productImport->getAeProductStock());
        $this->assertSame(Factory::AE_SKU_PRICE, $productImport->getAeSkuPrice());
        $this->assertSame(Factory::AE_OFFER_SALE_PRICE, $productImport->getAeOfferSalePrice());
        $this->assertSame(Factory::AE_OFFER_BULK_SALE_PRICE, $productImport->getAeOfferBulkSalePrice());
        $this->assertSame(Factory::AE_SKU_CURRENCY_CODE, $productImport->getAeSkuCurrencyCode());
        $this->assertSame(Factory::AE_FREIGHT_CODE, $productImport->getAeFreightCode());
        $this->assertSame(Factory::AE_SHIPPING_FEE, $productImport->getAeShippingFee());
        $this->assertSame(Factory::AE_SHIPPING_FEE_CURRENCY, $productImport->getAeShippingFeeCurrency());
        $this->assertEmpty($productImport->getAeVariantAttributes());
        $this->assertEmpty($productImport->getAeProductImageUrls());
        $this->assertNotNull($productImport->getCreatedAt());
        $this->assertNotNull($productImport->getUpdatedAt());
    }

    public function testSetters(): void
    {
        $newCreatedAt = new DateTime();
        $newUpdatedAt = new DateTime();
        $productImport = Factory::createAeProductImportProduct();

        /** @var AeProductImportProductAttribute[] $attributes */
        $attributes = [Factory::createAeProductImportProductAttribute(aeProductImportProduct: $productImport)];
        $imageUrls = [Factory::AE_IMAGE_URL => true];

        $productImport->setAeProductId(Factory::NEW_AE_PRODUCT_ID);
        $productImport->setAeSkuId(Factory::NEW_AE_SKU_ID);
        $productImport->setAeSkuAttr(Factory::NEW_AE_SKU_ATTR);
        $productImport->setAeSkuCode(Factory::NEW_AE_SKU_CODE);
        $productImport->setNbProductId(Factory::NEW_NB_PRODUCT_ID);
        $productImport->setAeProductName(Factory::NEW_AE_PRODUCT_NAME);
        $productImport->setAeProductDescription(Factory::NEW_AE_PRODUCT_DESCRIPTION);
        $productImport->setAeProductCategoryName(Factory::NEW_AE_PRODUCT_CATEGORY_NAME);
        $productImport->setAeProductBarcode(Factory::NEW_AE_PRODUCT_BARCODE);
        $productImport->setAeProductWeight(Factory::NEW_AE_PRODUCT_WEIGHT);
        $productImport->setAeProductLength(Factory::NEW_AE_PRODUCT_LENGTH);
        $productImport->setAeProductWidth(Factory::NEW_AE_PRODUCT_WIDTH);
        $productImport->setAeProductHeight(Factory::NEW_AE_PRODUCT_HEIGHT);
        $productImport->setAeProductStock(Factory::NEW_AE_PRODUCT_STOCK);
        $productImport->setAeSkuPrice(Factory::NEW_AE_SKU_PRICE);
        $productImport->setAeOfferSalePrice(Factory::NEW_AE_OFFER_SALE_PRICE);
        $productImport->setAeOfferBulkSalePrice(Factory::NEW_AE_OFFER_BULK_SALE_PRICE);
        $productImport->setAeSkuCurrencyCode(Factory::NEW_AE_SKU_CURRENCY_CODE);
        $productImport->setAeFreightCode(Factory::NEW_AE_FREIGHT_CODE);
        $productImport->setAeShippingFee(Factory::NEW_AE_SHIPPING_FEE);
        $productImport->setAeShippingFeeCurrency(Factory::NEW_AE_SHIPPING_FEE_CURRENCY);
        $productImport->setAeVariantAttributes($attributes);
        $productImport->setAeProductImageUrls($imageUrls);
        $productImport->setCreatedAt($newCreatedAt);
        $productImport->setUpdatedAt($newUpdatedAt);

        $this->assertSame(Factory::NEW_AE_PRODUCT_ID, $productImport->getAeProductId());
        $this->assertSame(Factory::NEW_AE_SKU_ID, $productImport->getAeSkuId());
        $this->assertSame(Factory::NEW_AE_SKU_ATTR, $productImport->getAeSkuAttr());
        $this->assertSame(Factory::NEW_AE_SKU_CODE, $productImport->getAeSkuCode());
        $this->assertSame(Factory::NEW_NB_PRODUCT_ID, $productImport->getNbProductId());
        $this->assertSame(Factory::NEW_AE_PRODUCT_NAME, $productImport->getAeProductName());
        $this->assertSame(Factory::NEW_AE_PRODUCT_DESCRIPTION, $productImport->getAeProductDescription());
        $this->assertSame(Factory::NEW_AE_PRODUCT_CATEGORY_NAME, $productImport->getAeProductCategoryName());
        $this->assertSame(Factory::NEW_AE_PRODUCT_BARCODE, $productImport->getAeProductBarcode());
        $this->assertSame(Factory::NEW_AE_PRODUCT_WEIGHT, $productImport->getAeProductWeight());
        $this->assertSame(Factory::NEW_AE_PRODUCT_LENGTH, $productImport->getAeProductLength());
        $this->assertSame(Factory::NEW_AE_PRODUCT_WIDTH, $productImport->getAeProductWidth());
        $this->assertSame(Factory::NEW_AE_PRODUCT_HEIGHT, $productImport->getAeProductHeight());
        $this->assertSame(Factory::NEW_AE_PRODUCT_STOCK, $productImport->getAeProductStock());
        $this->assertSame(Factory::NEW_AE_SKU_PRICE, $productImport->getAeSkuPrice());
        $this->assertSame(Factory::NEW_AE_OFFER_SALE_PRICE, $productImport->getAeOfferSalePrice());
        $this->assertSame(Factory::NEW_AE_OFFER_BULK_SALE_PRICE, $productImport->getAeOfferBulkSalePrice());
        $this->assertSame(Factory::NEW_AE_SKU_CURRENCY_CODE, $productImport->getAeSkuCurrencyCode());
        $this->assertSame(Factory::NEW_AE_FREIGHT_CODE, $productImport->getAeFreightCode());
        $this->assertSame(Factory::NEW_AE_SHIPPING_FEE, $productImport->getAeShippingFee());
        $this->assertSame(Factory::NEW_AE_SHIPPING_FEE_CURRENCY, $productImport->getAeShippingFeeCurrency());
        $this->assertSame($attributes, $productImport->getAeVariantAttributes());
        $this->assertSame($attributes[0]->getAeProductImportProduct(), $productImport);
        $this->assertSame($imageUrls, $productImport->getAeProductImageUrls());
        $this->assertSame($productImport->getCreatedAt(), $newCreatedAt);
        $this->assertSame($productImport->getUpdatedAt(), $newUpdatedAt);
    }
}
