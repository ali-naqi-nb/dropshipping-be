<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\Product\AliExpressProductImport;

use App\Application\Command\Product\AliExpressProductImport\CreateAliExpressProductGroupCommand;
use App\Domain\Model\Product\AeProductImportGroupProductData;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Unit\UnitTestCase;

final class CreateAliExpressProductGroupCommandTest extends UnitTestCase
{
    public function testGetter(): void
    {
        $product = new AeProductImportGroupProductData(
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

        $command = new CreateAliExpressProductGroupCommand(products: [$product]);

        $this->assertEquals($product, $command->getProducts()[0]);
    }
}
