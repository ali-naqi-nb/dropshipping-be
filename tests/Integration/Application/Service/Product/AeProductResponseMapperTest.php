<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Service\Product;

use App\Application\Service\AliExpress\AeUtil;
use App\Application\Service\Product\AeProductResponseMapper;
use App\Domain\Model\Product\AeProductImportProductAttribute;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;

final class AeProductResponseMapperTest extends IntegrationTestCase
{
    private AeProductResponseMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AeProductResponseMapper $mapper */
        $mapper = self::getContainer()->get(AeProductResponseMapper::class);
        $this->mapper = $mapper;
    }

    public function testGetResponse(): void
    {
        $aeProduct = Factory::createAeProductImportProduct();
        /** @var AeProductImportProductAttribute[] $aeAttributes */
        $aeAttributes = [Factory::createAeProductImportProductAttribute(aeProductImportProduct: $aeProduct)];
        /** @var array<string, bool> $images */
        $images = [Factory::AE_IMAGE_URL => false, Factory::NEW_AE_IMAGE_URL => true];

        $aeProduct->setAeVariantAttributes($aeAttributes);
        $aeProduct->setAeProductImageUrls($images);

        $aeDeliveryOptions = [Factory::createAeDeliveryOption()];

        $response = $this->mapper->getResponse($aeProduct, $aeDeliveryOptions);

        $this->assertSame($aeProduct->getAeProductId(), $response->getAeProductId());
        $this->assertSame($aeProduct->getAeSkuId(), $response->getAeSkuId());
        $this->assertSame($aeProduct->getAeProductName(), $response->getAeProductName());
        $this->assertSame($aeProduct->getAeProductCategoryName(), $response->getAeProductCategoryName());
        $this->assertSame($aeProduct->getAeProductStock(), $response->getAeSkuStock());
        $this->assertSame($aeProduct->getAeSkuPrice(), $response->getAeSkuPrice());
        $this->assertSame($aeProduct->getAeSkuCurrencyCode(), $response->getAeSkuPriceCurrency());
        $this->assertSame($aeAttributes[0]->getAeAttributeName(), $response->getAeVariantAttributes()[0]->getAeVariantAttributeName());
        $this->assertSame($aeAttributes[0]->getAeAttributeType()->value, $response->getAeVariantAttributes()[0]->getAeVariantAttributeType());
        $this->assertSame($aeAttributes[0]->getAeAttributeValue(), $response->getAeVariantAttributes()[0]->getAeVariantAttributeValue());
        $this->assertSame(Factory::NEW_AE_IMAGE_URL, $response->getAeProductImageUrls()[0]);
        $this->assertSame(Factory::AE_IMAGE_URL, $response->getAeProductImageUrls()[1]);
        $this->assertSame($aeDeliveryOptions[0]['code'], $response->getAeProductShippingOptions()[0]->getCode());
        $this->assertSame($aeDeliveryOptions[0]['ship_from_country'], $response->getAeProductShippingOptions()[0]->getShipsFrom());
        $this->assertSame($aeDeliveryOptions[0]['max_delivery_days'], $response->getAeProductShippingOptions()[0]->getMaxDeliveryDays());
        $this->assertSame($aeDeliveryOptions[0]['min_delivery_days'], $response->getAeProductShippingOptions()[0]->getMinDeliveryDays());
        $this->assertSame(AeUtil::toBase100($aeDeliveryOptions[0]['shipping_fee_cent']), $response->getAeProductShippingOptions()[0]->getShippingFeePrice());
        $this->assertSame($aeDeliveryOptions[0]['shipping_fee_currency'], $response->getAeProductShippingOptions()[0]->getShippingFeeCurrency());
    }
}
