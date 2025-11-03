<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

final class AeOrderFactory
{
    public const PLACE_ORDER_API_NAME = 'aliexpress.trade.buy.placeorder';
    public const LOGISTICS_ADDRESS = [
        'address' => 'Addy 1',
        'address2' => '',
        'city' => 'City 1',
        'contact_person' => '',
        'country' => 'Country 1',
        'cpf' => null,
        'full_name' => '',
        'locale' => null,
        'mobile_no' => '',
        'passport_no' => null,
        'passport_organization' => null,
        'phone_country' => null,
        'province' => 'Province 1',
        'tax_number' => null,
        'zip' => '',
        'rut_no' => null,
        'foreigner_passport_no' => null,
        'vat_no' => '',
        'tax_company' => '',
        'location_tree_address_id' => null,
    ];

    public const PRODUCT_ITEMS = [
        [
            'product_id' => 122222222,
            'sku_attr' => 33,
            'logistics_service_name' => 'code',
            'product_count' => 2,
        ],
    ];

    public const SHIPPING_ADDRESS = [
        'firstName' => 'Amanda',
        'lastName' => 'Smith',
        'address' => '123 Maple Street',
        'addressAdditions' => 'Apt 4B',
        'city' => 'Springfield',
        'country' => 'USA',
        'phone' => '+1 555 789 6543',
        'area' => 'Downtown',
        'postCode' => '62704',
        'companyVat' => 'US123456789',
        'companyName' => 'Tech Solutions LLC',
    ];

    public const AliExpressGetProductResponse = [
        'ae_item_sku_info_dtos' => [
            'ae_item_sku_info_d_t_o' => [
                [
                    'sku_id' => 1,
                    'sku_available_stock' => 23,
                    'sku_price' => '2345',
                    'currency_code' => 'USD',
                ],
                [
                    'sku_id' => 2,
                    'sku_available_stock' => 23,
                    'sku_price' => '2345',
                    'currency_code' => 'USD',
                ],
            ],
        ],
    ];

    public static function getPlaceOrderPayload(
        array $logistics_address = self::LOGISTICS_ADDRESS,
        array $product_items = self::PRODUCT_ITEMS
    ): array {
        return [
            'logistics_address' => $logistics_address,
            'product_items' => $product_items,
        ];
    }
}
