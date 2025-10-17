<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\AeProductImportGroupProductData;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Shared\Factory\ProductTypeFactory;
use App\Tests\Unit\UnitTestCase;

final class AeProductImportGroupProductDataTest extends UnitTestCase
{
    public function testGettersAndSetters(): void
    {
        $aeProductImportGroupProductData = new AeProductImportGroupProductData(
            aeProductId: AeProductImportFactory::AE_PRODUCT_ID,
            aeSkuId: AeProductImportProductFactory::AE_SKU_ID,
            name: AeProductImportProductFactory::NEW_AE_PRODUCT_NAME,
            description: AeProductImportProductFactory::NEW_AE_PRODUCT_DESCRIPTION,
            sku: AeProductImportProductFactory::AE_IMPORT_SKU_CODE,
            price: AeProductImportProductFactory::AE_SKU_PRICE,
            mainCategoryId: ProductFactory::CATEGORY_ID_FIRST,
            additionalCategories: [ProductFactory::CATEGORY_ID_SECOND],
            stock: AeProductImportProductFactory::AE_PRODUCT_STOCK,
            barcode: AeProductImportProductFactory::NEW_AE_PRODUCT_BARCODE,
            weight: AeProductImportProductFactory::AE_PRODUCT_WEIGHT,
            length: AeProductImportProductFactory::AE_PRODUCT_LENGTH,
            width: AeProductImportProductFactory::AE_IMPORT_PACKAGE_WIDTH,
            height: AeProductImportProductFactory::AE_PRODUCT_HEIGHT,
            costPerItem: AeProductImportProductFactory::AE_SKU_PRICE,
            productTypeName: AeProductImportProductFactory::PRODUCT_TYPE_NAME,
            attributes: AeProductImportFactory::ATTRIBUTES,
            images: [AeProductImportProductFactory::AE_IMAGE_URL]
        );

        $this->assertSame(AeProductImportFactory::AE_PRODUCT_ID, $aeProductImportGroupProductData->getAeProductId());
        $this->assertSame(AeProductImportProductFactory::AE_SKU_ID, $aeProductImportGroupProductData->getAeSkuId());
        $this->assertSame(AeProductImportProductFactory::NEW_AE_PRODUCT_NAME, $aeProductImportGroupProductData->getName());
        $this->assertSame(AeProductImportProductFactory::NEW_AE_PRODUCT_DESCRIPTION, $aeProductImportGroupProductData->getDescription());
        $this->assertSame(AeProductImportProductFactory::AE_IMPORT_SKU_CODE, $aeProductImportGroupProductData->getSku());
        $this->assertSame(AeProductImportProductFactory::AE_SKU_PRICE, $aeProductImportGroupProductData->getPrice());
        $this->assertSame(ProductFactory::CATEGORY_ID_FIRST, $aeProductImportGroupProductData->getMainCategoryId());
        $this->assertSame([ProductFactory::CATEGORY_ID_SECOND], $aeProductImportGroupProductData->getAdditionalCategories());
        $this->assertSame(AeProductImportProductFactory::AE_PRODUCT_STOCK, $aeProductImportGroupProductData->getStock());
        $this->assertSame(AeProductImportProductFactory::NEW_AE_PRODUCT_BARCODE, $aeProductImportGroupProductData->getBarcode());
        $this->assertSame(AeProductImportProductFactory::AE_PRODUCT_WEIGHT, $aeProductImportGroupProductData->getWeight());
        $this->assertSame(AeProductImportProductFactory::AE_PRODUCT_LENGTH, $aeProductImportGroupProductData->getLength());
        $this->assertSame(AeProductImportProductFactory::AE_IMPORT_PACKAGE_WIDTH, $aeProductImportGroupProductData->getWidth());
        $this->assertSame(AeProductImportProductFactory::AE_PRODUCT_HEIGHT, $aeProductImportGroupProductData->getHeight());
        $this->assertSame(AeProductImportProductFactory::AE_SKU_PRICE, $aeProductImportGroupProductData->getCostPerItem());
        $this->assertSame(AeProductImportProductFactory::PRODUCT_TYPE_NAME, $aeProductImportGroupProductData->getProductTypeName());
        $this->assertSame(AeProductImportFactory::ATTRIBUTES, $aeProductImportGroupProductData->getAttributes());
        $this->assertSame([AeProductImportProductFactory::AE_IMAGE_URL], $aeProductImportGroupProductData->getImages());

        $aeProductImportGroupProductData->setAeProductId(AeProductImportProductFactory::NEW_AE_PRODUCT_ID);
        $this->assertSame(AeProductImportProductFactory::NEW_AE_PRODUCT_ID, $aeProductImportGroupProductData->getAeProductId());

        $aeProductImportGroupProductData->setAeSkuId(AeProductImportProductFactory::NEW_AE_SKU_ID);
        $this->assertSame(AeProductImportProductFactory::NEW_AE_SKU_ID, $aeProductImportGroupProductData->getAeSkuId());

        $aeProductImportGroupProductData->setName(AeProductImportProductFactory::NEW_AE_PRODUCT_NAME);
        $this->assertSame(AeProductImportProductFactory::NEW_AE_PRODUCT_NAME, $aeProductImportGroupProductData->getName());

        $aeProductImportGroupProductData->setDescription(AeProductImportProductFactory::AE_PRODUCT_DESCRIPTION);
        $this->assertSame(AeProductImportProductFactory::AE_PRODUCT_DESCRIPTION, $aeProductImportGroupProductData->getDescription());

        $aeProductImportGroupProductData->setSku(AeProductImportProductFactory::AE_IMPORT_SKU_PROPERTY_NAME_0);
        $this->assertSame(AeProductImportProductFactory::AE_IMPORT_SKU_PROPERTY_NAME_0, $aeProductImportGroupProductData->getSku());

        $aeProductImportGroupProductData->setPrice(AeProductImportProductFactory::AE_SKU_PRICE);
        $this->assertSame(AeProductImportProductFactory::AE_SKU_PRICE, $aeProductImportGroupProductData->getPrice());

        $aeProductImportGroupProductData->setMainCategoryId(ProductFactory::CATEGORY_ID_FIRST);
        $this->assertSame(ProductFactory::CATEGORY_ID_FIRST, $aeProductImportGroupProductData->getMainCategoryId());

        $aeProductImportGroupProductData->setAdditionalCategories([ProductFactory::CATEGORY_ID_SECOND]);
        $this->assertSame([ProductFactory::CATEGORY_ID_SECOND], $aeProductImportGroupProductData->getAdditionalCategories());

        $aeProductImportGroupProductData->setStock(ProductFactory::STOCK);
        $this->assertSame(ProductFactory::STOCK, $aeProductImportGroupProductData->getStock());

        $aeProductImportGroupProductData->setBarcode(ProductFactory::BARCODE);
        $this->assertSame(ProductFactory::BARCODE, $aeProductImportGroupProductData->getBarcode());

        $aeProductImportGroupProductData->setWeight(ProductFactory::WEIGHT);
        $this->assertSame(ProductFactory::WEIGHT, $aeProductImportGroupProductData->getWeight());

        $aeProductImportGroupProductData->setWidth(ProductFactory::WIDTH_INT);
        $this->assertSame(ProductFactory::WIDTH_INT, $aeProductImportGroupProductData->getWidth());

        $aeProductImportGroupProductData->setHeight(ProductFactory::HEIGHT_INT);
        $this->assertSame(ProductFactory::HEIGHT_INT, $aeProductImportGroupProductData->getHeight());

        $aeProductImportGroupProductData->setCostPerItem(ProductFactory::COST_PER_ITEM);
        $this->assertSame(ProductFactory::COST_PER_ITEM, $aeProductImportGroupProductData->getCostPerItem());

        $aeProductImportGroupProductData->setProductTypeName(ProductTypeFactory::NAME);
        $this->assertSame(ProductTypeFactory::NAME, $aeProductImportGroupProductData->getProductTypeName());

        $aeProductImportGroupProductData->setAttributes(AeProductImportFactory::ATTRIBUTES);
        $this->assertSame(AeProductImportFactory::ATTRIBUTES, $aeProductImportGroupProductData->getAttributes());

        $aeProductImportGroupProductData->setImages([AeProductImportProductFactory::AE_IMAGE_URL]);
        $this->assertSame([AeProductImportProductFactory::AE_IMAGE_URL], $aeProductImportGroupProductData->getImages());

        $aeProductImportGroupProductData->setLength(ProductFactory::LENGTH_INT);
        $this->assertSame(ProductFactory::LENGTH_INT, $aeProductImportGroupProductData->getLength());
    }
}
