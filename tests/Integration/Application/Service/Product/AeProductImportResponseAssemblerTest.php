<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Service\Product;

use App\Application\Service\AliExpress\AeUtil;
use App\Application\Service\Product\AeProductImportResponseAssembler;
use App\Domain\Model\Product\AeProductImportProductAttribute;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;

final class AeProductImportResponseAssemblerTest extends IntegrationTestCase
{
    private AeProductImportResponseAssembler $assembler;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AeProductImportResponseAssembler $assembler */
        $assembler = self::getContainer()->get(AeProductImportResponseAssembler::class);
        $this->assembler = $assembler;
    }

    public function testAssembleAeProductResponse(): void
    {
        $aeProduct = Factory::createAeProductImportProduct();
        /** @var AeProductImportProductAttribute[] $aeAttributes */
        $aeAttributes = [Factory::createAeProductImportProductAttribute(aeProductImportProduct: $aeProduct)];
        /** @var array<string, bool> $images */
        $images = [Factory::AE_IMAGE_URL => false, Factory::NEW_AE_IMAGE_URL => true];

        $aeProduct->setAeVariantAttributes($aeAttributes);
        $aeProduct->setAeProductImageUrls($images);

        $aeDeliveryOptions = [Factory::createAeDeliveryOption()];

        $aeProducts = [$aeProduct];
        $skuDeliveryOptions = [Factory::AE_SKU_ID => $aeDeliveryOptions];

        $response = $this->assembler->assembleAeProductResponse($aeProducts, $skuDeliveryOptions);

        $this->assertSame($aeProduct->getAeProductId(), $response->getItems()[0]->getAeProductId());
        $this->assertSame($aeProduct->getAeSkuId(), $response->getItems()[0]->getAeSkuId());
        $this->assertSame($aeProduct->getAeProductName(), $response->getItems()[0]->getAeProductName());
        $this->assertSame($aeProduct->getAeProductCategoryName(), $response->getItems()[0]->getAeProductCategoryName());
        $this->assertSame($aeProduct->getAeProductStock(), $response->getItems()[0]->getAeSkuStock());
        $this->assertSame($aeProduct->getAeSkuPrice(), $response->getItems()[0]->getAeSkuPrice());
        $this->assertSame($aeProduct->getAeSkuCurrencyCode(), $response->getItems()[0]->getAeSkuPriceCurrency());
        $this->assertSame($aeAttributes[0]->getAeAttributeName(), $response->getItems()[0]->getAeVariantAttributes()[0]->getAeVariantAttributeName());
        $this->assertSame($aeAttributes[0]->getAeAttributeType()->value, $response->getItems()[0]->getAeVariantAttributes()[0]->getAeVariantAttributeType());
        $this->assertSame($aeAttributes[0]->getAeAttributeValue(), $response->getItems()[0]->getAeVariantAttributes()[0]->getAeVariantAttributeValue());
        $this->assertSame(Factory::NEW_AE_IMAGE_URL, $response->getItems()[0]->getAeProductImageUrls()[0]);
        $this->assertSame(Factory::AE_IMAGE_URL, $response->getItems()[0]->getAeProductImageUrls()[1]);
        $this->assertSame($skuDeliveryOptions[Factory::AE_SKU_ID][0]['code'], $response->getItems()[0]->getAeProductShippingOptions()[0]->getCode());
        $this->assertSame($skuDeliveryOptions[Factory::AE_SKU_ID][0]['ship_from_country'], $response->getItems()[0]->getAeProductShippingOptions()[0]->getShipsFrom());
        $this->assertSame($skuDeliveryOptions[Factory::AE_SKU_ID][0]['max_delivery_days'], $response->getItems()[0]->getAeProductShippingOptions()[0]->getMaxDeliveryDays());
        $this->assertSame($skuDeliveryOptions[Factory::AE_SKU_ID][0]['min_delivery_days'], $response->getItems()[0]->getAeProductShippingOptions()[0]->getMinDeliveryDays());
        $this->assertSame(AeUtil::toBase100($skuDeliveryOptions[Factory::AE_SKU_ID][0]['shipping_fee_cent']), $response->getItems()[0]->getAeProductShippingOptions()[0]->getShippingFeePrice());
        $this->assertSame($skuDeliveryOptions[Factory::AE_SKU_ID][0]['shipping_fee_currency'], $response->getItems()[0]->getAeProductShippingOptions()[0]->getShippingFeeCurrency());
    }

    public function testAssembleAeProductResponseFiltersProductsWithoutDeliveryOptions(): void
    {
        $aeProduct1 = Factory::createAeProductImportProduct();
        $aeProduct2 = Factory::createAeProductImportProduct(aeSkuId: Factory::AE_SKU_ID + 1);
        $aeProduct3 = Factory::createAeProductImportProduct(aeSkuId: Factory::AE_SKU_ID + 2);

        $aeDeliveryOptions = [Factory::createAeDeliveryOption()];

        $aeProducts = [$aeProduct1, $aeProduct2, $aeProduct3];
        $skuDeliveryOptions = [
            Factory::AE_SKU_ID => $aeDeliveryOptions,      
            Factory::AE_SKU_ID + 1 => [],
            Factory::AE_SKU_ID + 2 => []
        ];

        $response = $this->assembler->assembleAeProductResponse($aeProducts, $skuDeliveryOptions);

        $this->assertCount(1, $response->getItems());
        $this->assertSame($aeProduct1->getAeSkuId(), $response->getItems()[0]->getAeSkuId());
    }
}
