<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Product\AeAttributeType;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductAttribute;
use App\Domain\Model\Product\AeProductImportProductImage;
use App\Tests\Shared\Trait\Assertions\AssertionPatternsInterface;
use DateTime;

final class AeProductImportProductFactory
{
    public const NON_EXISTING_ID = '8943ab0a-44e8-4d92-b916-5e0ab5594467';
    public const AE_PRODUCT_ID = 1005007433426570;
    public const AE_SKU_ID = 12000040736583479;
    public const AE_SKU_ATTR = '14:193#W32 khaki;200000124:200000337';
    public const AE_SKU_CODE = '14:771;200000124:200000338';
    public const NB_PRODUCT_ID = '2e38a954-3268-483d-9d24-c2de79cd03e8';
    public const AE_PRODUCT_NAME = 'Spring Men\'s Shoes';
    public const AE_PRODUCT_DESCRIPTION = '<div>Crafted from rubber, these shoes offer superior breathability, ensuring your feet stay cool and comfortable throughout the day.</div>';
    public const AE_PRODUCT_CATEGORY_NAME = 'Men\'s Shoes';
    public const AE_PRODUCT_BARCODE = null;
    public const AE_PRODUCT_WEIGHT = 0_01;
    public const AE_PRODUCT_LENGTH = 12_00;
    public const AE_PRODUCT_WIDTH = 23_00;
    public const AE_PRODUCT_HEIGHT = 12_00;
    public const AE_PRODUCT_STOCK = 56;
    public const AE_SKU_PRICE = 12_23;
    public const AE_OFFER_SALE_PRICE = 5_61;
    public const AE_OFFER_BULK_SALE_PRICE = 5_61;
    public const AE_SKU_CURRENCY_CODE = CurrencyFactory::USD;
    public const AE_FREIGHT_CODE = 'freight-code';
    public const AE_SHIPPING_FEE = 5_00;
    public const AE_SHIPPING_FEE_CURRENCY = CurrencyFactory::USD;
    public const AE_IMAGE_URL = 'https://ae01.alicdn.com/kf/Sb65e8c3e8fb64e66ae10d05c93dff1ecn.jpg';
    public const AE_IMAGE_IS_MAIN = true;
    public const AE_ATTRIBUTE_TYPE = AeAttributeType::SkuProperty;
    public const AE_ATTRIBUTE_NAME = 'Color';
    public const AE_ATTRIBUTE_VALUE = 'black';
    public const AE_CATEGORY_ID = 200131145;

    public const NEW_AE_PRODUCT_ID = 1005007433426577;
    public const NEW_AE_SKU_ID = 12000040736583477;
    public const NEW_AE_SKU_ATTR = '19:193#W32 khaki;200000124:200000337';
    public const NEW_AE_SKU_CODE = '19:771;200000124:200000338';
    public const NEW_NB_PRODUCT_ID = '5d8a62a1-9a13-459a-ac14-07886db74610';
    public const NEW_AE_PRODUCT_NAME = 'Summer Men\'s Shoes ';
    public const NEW_AE_PRODUCT_DESCRIPTION = '<div>Crafted from rubber, these shoes offer superior breathability</div>';
    public const NEW_AE_PRODUCT_CATEGORY_NAME = 'Men\'s Shoes 2';
    public const NEW_AE_PRODUCT_BARCODE = 'ABJ23UIUZH1Z';
    public const NEW_AE_PRODUCT_WEIGHT = 0_02;
    public const NEW_AE_PRODUCT_LENGTH = 12_01;
    public const NEW_AE_PRODUCT_WIDTH = 23_01;
    public const NEW_AE_PRODUCT_HEIGHT = 12_01;
    public const NEW_AE_PRODUCT_STOCK = 57;
    public const NEW_AE_SKU_PRICE = 12_23;
    public const NEW_AE_OFFER_SALE_PRICE = 5_61;
    public const NEW_AE_OFFER_BULK_SALE_PRICE = 5_61;
    public const NEW_AE_SKU_CURRENCY_CODE = CurrencyFactory::BGN;
    public const NEW_AE_FREIGHT_CODE = 'freight-code2';
    public const NEW_AE_SHIPPING_FEE = 5_01;
    public const NEW_AE_SHIPPING_FEE_CURRENCY = CurrencyFactory::BGN;
    public const NEW_AE_IMAGE_URL = 'https://ae01.alicdn.com/kf/S03e0fecd31644c0698dadc4f6a85532fF.jpg';
    public const NEW_AE_IMAGE_IS_MAIN = false;
    public const NEW_AE_ATTRIBUTE_TYPE = AeAttributeType::SkuProperty;
    public const NEW_AE_ATTRIBUTE_NAME = 'Upper Material';
    public const NEW_AE_ATTRIBUTE_VALUE = 'RUBBER';

    public const PRODUCT_TYPE_NAME = 'leather';

    public const AE_DELIVERY_CODE = 'CAINIAO_FULFILLMENT_STD';
    public const AE_DELIVERY_SHIP_FROM = 'CN';
    public const AE_DELIVERY_MIN_DELIVERY_DAYS = 12;
    public const AE_DELIVERY_MAX_DELIVERY_DAYS = 19;
    public const AE_DELIVERY_SHIPPING_FEE_CURRENCY = CurrencyFactory::USD;
    public const AE_DELIVERY_SHIPPING_FEE = 1_99;
    public const AE_DELIVERY_IS_FREE_SHIPPING = false;

    public const AE_PRODUCT_URL = 'https://www.aliexpress.com/item/1005007433426570.html';
    public const AE_PRODUCT_SHIPS_TO = 'BG';
    public const AE_PRODUCT_SOURCE = 'CN';

    public const AE_PRODUCT_TEST_ERROR = 7777000777000777;
    public const AE_CATEGORY_TEST_ERROR = 777000777;
    public const AE_SKU_TEST_ERROR = 77700077700000777;
    public const AE_PRODUCT_404_ERROR = 7777404777404777;
    public const AE_PRODUCT_URL_TEST_ERROR = 'https://www.aliexpress.com/item/7777000777000777.html';

    public const AE_IMPORT_SKU_ATTR = '14:193#W32 khaki;200000124:200000338';
    public const AE_IMPORT_SKU_ID = '12000040736583479';
    public const AE_IMPORT_CURRENCY_CODE = 'USD';
    public const AE_IMPORT_SKU_PRICE = '13.52';
    public const AE_IMPORT_OFFER_SALE_PRICE = '5.61';
    public const AE_IMPORT_OFFER_BULK_SALE_PRICE = '5.61';
    public const AE_IMPORT_SKU_AVAILABLE_STOCK = 0;
    public const AE_IMPORT_SKU_CODE = '14:193;200000124:200000338';
    public const AE_IMPORT_SKU_IMAGE_0 = 'https://ae01.alicdn.com/kf/S03e0fecd31644c0698dadc4f6a85532fF.jpg';
    public const AE_IMPORT_SKU_PROPERTY_NAME_0 = 'Color';
    public const AE_IMPORT_SKU_PROPERTY_VALUE_0 = 'W32 khaki';
    public const AE_IMPORT_SKU_PROPERTY_NAME_1 = 'Shoe Size';
    public const AE_IMPORT_SKU_PROPERTY_VALUE_1 = '43';
    public const AE_IMPORT_SKU_PROPERTY_NAME_2 = 'Upper Material';
    public const AE_IMPORT_SKU_PROPERTY_VALUE_2 = 'RUBBER';
    public const AE_IMPORT_SUBJECT = 'Spring Men\'s Shoes New Breathable Work Safety Shoes Trendy Versatile Non-slip Sports Comfortable Eva Insoles Rubber Upper';
    public const AE_IMPORT_DETAIL = '<div class="detailmodule_html"><div class="detail-desc-decorate-richtext"><div><div><p style="font-family:&quot;Open Sans&quot;, sans-serif;font-size:14px;font-weight:400;letter-spacing:normal;line-height:inherit;text-align:start;white-space:normal;color:rgb(0, 0, 0);background-color:rgb(255, 255, 255);margin:0px;margin-bottom:0px;margin-top:0px;margin-left:0px;margin-right:0px;padding:0px;padding-bottom:0px;padding-top:0px;padding-left:0px;padding-right:0px;box-sizing:border-box" align="start"><span style="background-color:rgb(255, 255, 255)"><strong>• Breathable Material :</strong></span><span style="background-color:rgb(255, 255, 255)">Crafted from rubber, these shoes offer superior breathability, ensuring your feet stay cool and comfortable throughout the day.</span></p><br/><p style="font-family:&quot;Open Sans&quot;, sans-serif;font-size:14px;font-weight:400;letter-spacing:normal;line-height:inherit;text-align:start;white-space:normal;color:rgb(0, 0, 0);background-color:rgb(255, 255, 255);margin:0px;margin-bottom:0px;margin-top:0px;margin-left:0px;margin-right:0px;padding:0px;padding-bottom:0px;padding-top:0px;padding-left:0px;padding-right:0px;box-sizing:border-box" align="start"><span style="background-color:rgb(255, 255, 255)"><strong>• Versatile Design :</strong></span><span style="background-color:rgb(255, 255, 255)">With their trendy all-match design, these shoes are perfect for any work-related task or casual outing, making them a dad&#x27;s ideal choice.</span></p><br/><p style="font-family:&quot;Open Sans&quot;, sans-serif;font-size:14px;font-weight:400;letter-spacing:normal;line-height:inherit;text-align:start;white-space:normal;color:rgb(0, 0, 0);background-color:rgb(255, 255, 255);margin:0px;margin-bottom:0px;margin-top:0px;margin-left:0px;margin-right:0px;padding:0px;padding-bottom:0px;padding-top:0px;padding-left:0px;padding-right:0px;box-sizing:border-box" align="start"><span style="background-color:rgb(255, 255, 255)"><strong>• Non-slip Feature :</strong></span><span style="background-color:rgb(255, 255, 255)">These sneakers come with a non-slip feature, providing stability and safety when walking on slippery surfaces.</span></p><br/><p style="font-family:&quot;Open Sans&quot;, sans-serif;font-size:14px;font-weight:400;letter-spacing:normal;line-height:inherit;text-align:start;white-space:normal;color:rgb(0, 0, 0);background-color:rgb(255, 255, 255);margin:0px;margin-bottom:0px;margin-top:0px;margin-left:0px;margin-right:0px;padding:0px;padding-bottom:0px;padding-top:0px;padding-left:0px;padding-right:0px;box-sizing:border-box" align="start"><span style="background-color:rgb(255, 255, 255)"><strong>• Labor Protection :</strong></span><span style="background-color:rgb(255, 255, 255)">Designed with labor protection in mind, these shoes offer durability and comfort, making them ideal for long hours of work.</span></p><br/><p style="font-family:&quot;Open Sans&quot;, sans-serif;font-size:14px;font-weight:400;letter-spacing:normal;line-height:inherit;text-align:start;white-space:normal;color:rgb(0, 0, 0);background-color:rgb(255, 255, 255);margin:0px;margin-bottom:0px;margin-top:0px;margin-left:0px;margin-right:0px;padding:0px;padding-bottom:0px;padding-top:0px;padding-left:0px;padding-right:0px;box-sizing:border-box" align="start"><span style="background-color:rgb(255, 255, 255)"><strong>• Summer-Ready :</strong></span><span style="background-color:rgb(255, 255, 255)">Perfect for summer, these shoes provide comfort and breathability, keeping your feet cool during the hot months.</span></p><br/><p style="font-family:&quot;Open Sans&quot;, sans-serif;font-size:14px;font-weight:400;letter-spacing:normal;line-height:inherit;text-align:start;white-space:normal;color:rgb(0, 0, 0);background-color:rgb(255, 255, 255);margin:0px;margin-bottom:0px;margin-top:0px;margin-left:0px;margin-right:0px;padding:0px;padding-bottom:0px;padding-top:0px;padding-left:0px;padding-right:0px;box-sizing:border-box" align="start"><span style="background-color:rgb(255, 255, 255)"><strong>• Origin :</strong></span><span style="background-color:rgb(255, 255, 255)"> Mainland China</span></p><br/></div></div><div><img src="https://ae01.alicdn.com/kf/S3e7fede349fd41658fcd88bd38f6e8df4.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Scc6bdd7deb9e4257ab8a952980f2fa416.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Se834f0e771374b83b81c3b865d0305d9e.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Sf390b1afe5614bbf89d5f299c461b91fl.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S5efef89b79994b5c99c19b7c291e8611k.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S49c967dfe83a4f0f9e8cb44baf902a9e3.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Se5dd94c5217441d9b6aa7316a8570e6dB.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S44d78fd5bde346aba4e5df73f148d960F.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Se0a134053c014aa98dfd063d3012ec74G.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S6b547bb66d5e460d94636246b111b309j.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S63c1472b264a4c1b900a3eb147e2d6bfU.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S865c1164701d4dbe829864b22fe52478w.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Sf6d07c06b36d4f31a93246d2aee1fa83U.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S7b517dba5e3d4864bfc1584f8ff04c32j.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S7f3d494548d440fcb2f7dcd51e1c18b0G.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Sa9cf661dbab14f7d859571a799459479d.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S86dcd1c82f734d27ac904b6d755664f70.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S0c5996336f65418b900ffe696160ca48X.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Sbe6d751ca1314fc0a5a35ea523c270b4C.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/S8e1f94b042d24acf942ddae422e46217H.jpg" slate-data-type="image"/> <img src="https://ae01.alicdn.com/kf/Sea85942ea0c14209b87e8a15ee212451C.jpg" slate-data-type="image"/></div></div></div><br/>';
    public const AE_IMPORT_CATEGORY_NAME = 'Men\'s Shoes';
    public const AE_IMPORT_PACKAGE_WIDTH = 23;
    public const AE_IMPORT_PACKAGE_HEIGHT = 12;
    public const AE_IMPORT_PACKAGE_LENGTH = 31;
    public const AE_IMPORT_GROSS_WEIGHT = '0.01';

    public const RESPONSE_PRODUCT_PATTERN = [
        'aeProductId' => '@integer@',
        'aeSkuId' => '@integer@',
        'aeProductName' => '@string@',
        'aeProductCategoryName' => '@string@',
        'aeSkuStock' => '@integer@',
        'aeSkuPrice' => '@integer@',
        'aeOfferSalePrice' => '@integer@',
        'aeOfferBulkSalePrice' => '@integer@',
        'aeSkuPriceCurrency' => AssertionPatternsInterface::CURRENCY,
        'aeVariantAttributes' => [
            [
                'aeVariantAttributeName' => '@string@',
                'aeVariantAttributeType' => '@string@',
                'aeVariantAttributeValue' => '@string@',
            ],
            '@...@',
        ],
        'aeProductImageUrls' => [
            AssertionPatternsInterface::URL,
            '@...@',
        ],
        'aeProductShippingOptions' => [
            [
                'code' => '@string@',
                'shipsFrom' => '@string@',
                'minDeliveryDays' => '@integer@',
                'maxDeliveryDays' => '@integer@',
                'shippingFeePrice' => '@integer@',
                'shippingFeeCurrency' => AssertionPatternsInterface::CURRENCY,
                'isFreeShipping' => '@boolean@',
            ],
            '@...@',
        ],
    ];

    public const RESPONSE_ITEMS_PATTERN = [
        self::RESPONSE_PRODUCT_PATTERN,
        '@...@',
    ];

    public static function createAeProductImportProduct(
        int $aeProductId = self::AE_PRODUCT_ID,
        int $aeSkuId = self::AE_SKU_ID,
        string $aeSkuAttr = self::AE_SKU_ATTR,
        ?string $aeSkuCode = self::AE_SKU_CODE,
        ?string $nbProductId = self::NB_PRODUCT_ID,
        string $aeProductName = self::AE_PRODUCT_NAME,
        ?string $aeProductDescription = self::AE_PRODUCT_DESCRIPTION,
        ?string $aeProductCategoryName = self::AE_PRODUCT_CATEGORY_NAME,
        ?string $aeProductBarcode = self::AE_PRODUCT_BARCODE,
        ?int $aeProductWeight = self::AE_PRODUCT_WEIGHT,
        ?int $aeProductLength = self::AE_PRODUCT_LENGTH,
        ?int $aeProductWidth = self::AE_PRODUCT_WIDTH,
        ?int $aeProductHeight = self::AE_PRODUCT_HEIGHT,
        int $aeProductStock = self::AE_PRODUCT_STOCK,
        ?int $aeSkuPrice = self::AE_SKU_PRICE,
        ?int $aeOfferSalePrice = self::AE_OFFER_SALE_PRICE,
        ?int $aeOfferBulkSalePrice = self::AE_OFFER_BULK_SALE_PRICE,
        ?string $aeSkuCurrencyCode = self::AE_SKU_CURRENCY_CODE,
        ?string $aeFreightCode = self::AE_FREIGHT_CODE,
        ?int $aeShippingFee = self::AE_SHIPPING_FEE,
        ?string $aeShippingFeeCurrency = self::AE_SHIPPING_FEE_CURRENCY,
        bool $withTimeStamps = true,
    ): AeProductImportProduct {
        $productImport = new AeProductImportProduct(
            aeProductId: $aeProductId,
            aeSkuId: $aeSkuId,
            aeSkuAttr: $aeSkuAttr,
            aeSkuCode: $aeSkuCode,
            nbProductId: $nbProductId,
            aeProductName: $aeProductName,
            aeProductDescription: $aeProductDescription,
            aeProductCategoryName: $aeProductCategoryName,
            aeProductBarcode: $aeProductBarcode,
            aeProductWeight: $aeProductWeight,
            aeProductLength: $aeProductLength,
            aeProductWidth: $aeProductWidth,
            aeProductHeight: $aeProductHeight,
            aeProductStock: $aeProductStock,
            aeSkuPrice: $aeSkuPrice,
            aeOfferSalePrice: $aeOfferSalePrice,
            aeOfferBulkSalePrice: $aeOfferBulkSalePrice,
            aeSkuCurrencyCode: $aeSkuCurrencyCode,
            aeFreightCode: $aeFreightCode,
            aeShippingFee: $aeShippingFee,
            aeShippingFeeCurrency: $aeShippingFeeCurrency,
        );

        if ($withTimeStamps) {
            $productImport->setCreatedAt(new DateTime());
            $productImport->setUpdatedAt(new DateTime());
        }

        return $productImport;
    }

    public static function createAeProductImportProductImage(
        ?AeProductImportProduct $aeProductImportProduct = null,
        string $aeImageUrl = self::AE_IMAGE_URL,
        bool $isMain = self::AE_IMAGE_IS_MAIN,
    ): AeProductImportProductImage {
        return new AeProductImportProductImage(
            aeProductImportProduct: $aeProductImportProduct ?? self::createAeProductImportProduct(),
            aeImageUrl: $aeImageUrl,
            isMain: $isMain,
        );
    }

    public static function createAeProductImportProductAttribute(
        AeProductImportProduct $aeProductImportProduct = null,
        AeAttributeType $aeAttributeType = self::AE_ATTRIBUTE_TYPE,
        string $aeAttributeName = self::AE_ATTRIBUTE_NAME,
        string $aeAttributeValue = self::AE_ATTRIBUTE_VALUE,
    ): AeProductImportProductAttribute {
        return new AeProductImportProductAttribute(
            aeProductImportProduct: $aeProductImportProduct ?? self::createAeProductImportProduct(),
            aeAttributeType: $aeAttributeType,
            aeAttributeName: $aeAttributeName,
            aeAttributeValue: $aeAttributeValue,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function createAeDeliveryOption(
        string $code = self::AE_DELIVERY_CODE,
        string $shipsFrom = self::AE_DELIVERY_SHIP_FROM,
        int $minDeliveryDays = self::AE_DELIVERY_MIN_DELIVERY_DAYS,
        int $maxDeliveryDays = self::AE_DELIVERY_MAX_DELIVERY_DAYS,
        int $shippingFeePrice = self::AE_DELIVERY_SHIPPING_FEE,
        string $shippingFeeCurrency = self::AE_DELIVERY_SHIPPING_FEE_CURRENCY,
        bool $isFreeShipping = self::AE_DELIVERY_IS_FREE_SHIPPING,
    ): array {
        return [
            'code' => $code,
            'ship_from_country' => $shipsFrom,
            'max_delivery_days' => $maxDeliveryDays,
            'min_delivery_days' => $minDeliveryDays,
            'shipping_fee_cent' => number_format($shippingFeePrice / 100, 2),
            'shipping_fee_currency' => $shippingFeeCurrency,
            'free_shipping' => $isFreeShipping,
        ];
    }
}
