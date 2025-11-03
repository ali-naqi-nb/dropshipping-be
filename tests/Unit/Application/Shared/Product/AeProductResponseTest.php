<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared\Product;

use App\Application\Shared\Product\AeProductResponse;
use App\Application\Shared\Product\AeShippingOptionResponse;
use App\Domain\Model\Product\AeProductImportProductAttribute;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class AeProductResponseTest extends UnitTestCase
{
    public function testFromAeProduct(): void
    {
        $aeProduct = Factory::createAeProductImportProduct();
        /** @var AeProductImportProductAttribute[] $aeAttributes */
        $aeAttributes = [Factory::createAeProductImportProductAttribute(aeProductImportProduct: $aeProduct)];
        /** @var array<string, bool> $images */
        $images = [Factory::AE_IMAGE_URL => false, Factory::NEW_AE_IMAGE_URL => true];

        $aeProduct->setAeVariantAttributes($aeAttributes);
        $aeProduct->setAeProductImageUrls($images);

        $shippingOptionResponse = [AeShippingOptionResponse::fromAeDeliveryOption(Factory::createAeDeliveryOption())];

        $response = AeProductResponse::fromAeProduct($aeProduct, $shippingOptionResponse);

        $this->assertSame($aeProduct->getAeProductId(), $response->getAeProductId());
        $this->assertSame($aeProduct->getAeSkuId(), $response->getAeSkuId());
        $this->assertSame($aeProduct->getAeProductName(), $response->getAeProductName());
        $this->assertSame($aeAttributes[0]->getAeAttributeValue(), $response->getVariantName());
        $this->assertSame($aeProduct->getAeProductCategoryName(), $response->getAeProductCategoryName());
        $this->assertSame($aeProduct->getAeProductStock(), $response->getAeSkuStock());
        $this->assertSame($aeProduct->getAeSkuPrice(), $response->getAeSkuPrice());
        $this->assertSame($aeProduct->getAeOfferSalePrice(), $response->getAeOfferSalePrice());
        $this->assertSame($aeProduct->getAeOfferBulkSalePrice(), $response->getAeOfferBulkSalePrice());
        $this->assertSame($aeProduct->getAeSkuCurrencyCode(), $response->getAeSkuPriceCurrency());
        $this->assertSame($aeAttributes[0]->getAeAttributeName(), $response->getAeVariantAttributes()[0]->getAeVariantAttributeName());
        $this->assertSame($aeAttributes[0]->getAeAttributeType()->value, $response->getAeVariantAttributes()[0]->getAeVariantAttributeType());
        $this->assertSame($aeAttributes[0]->getAeAttributeValue(), $response->getAeVariantAttributes()[0]->getAeVariantAttributeValue());
        $this->assertSame(Factory::NEW_AE_IMAGE_URL, $response->getAeProductImageUrls()[0]);
        $this->assertSame(Factory::AE_IMAGE_URL, $response->getAeProductImageUrls()[1]);
        $this->assertSame($shippingOptionResponse[0]->getCode(), $response->getAeProductShippingOptions()[0]->getCode());
        $this->assertSame($shippingOptionResponse[0]->getShipsFrom(), $response->getAeProductShippingOptions()[0]->getShipsFrom());
        $this->assertSame($shippingOptionResponse[0]->getMaxDeliveryDays(), $response->getAeProductShippingOptions()[0]->getMaxDeliveryDays());
        $this->assertSame($shippingOptionResponse[0]->getMinDeliveryDays(), $response->getAeProductShippingOptions()[0]->getMinDeliveryDays());
        $this->assertSame($shippingOptionResponse[0]->getShippingFeePrice(), $response->getAeProductShippingOptions()[0]->getShippingFeePrice());
        $this->assertSame($shippingOptionResponse[0]->getShippingFeeCurrency(), $response->getAeProductShippingOptions()[0]->getShippingFeeCurrency());
    }

    public function testFromAeProductWithoutVariants(): void
    {
        $aeProduct = Factory::createAeProductImportProduct();
        /** @var array<string, bool> $images */
        $images = [Factory::AE_IMAGE_URL => false];

        $aeProduct->setAeVariantAttributes([]);
        $aeProduct->setAeProductImageUrls($images);

        $response = AeProductResponse::fromAeProduct($aeProduct, []);

        $this->assertSame($aeProduct->getAeProductName(), $response->getAeProductName());
        $this->assertNull($response->getVariantName());
    }
}
