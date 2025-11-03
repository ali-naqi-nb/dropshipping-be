<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

final class OrderFactory
{
    public const NON_EXISTING_ORDER_ID = 'bbc17434-5827-4c4f-b610-7b5a8ae99e65';
    public const NON_EXISTING_ORDER_ID_1 = 'a49cf976-9495-4899-bd62-13d1beb38510';
    public const NON_EXISTING_ORDER_IDS = [
        self::NON_EXISTING_ORDER_ID,
        self::NON_EXISTING_ORDER_ID_1,
    ];

    public const LG_ALI_EXPRESS_PRODUCT_ID = '8943ab0a-44e8-4d92-b916-5e0ab5594467';
    public const HISENSE_ALI_EXPRESS_PRODUCT_ID = '1c872436-6812-489f-9c5c-82f9f139b5f9';
    public const DS_ORDER_SHIPPING_ADDRESS = [
        'country' => 'BG',
        'area' => 'София-град',
        'city' => 'София',
        'postCode' => '1000',
        'address' => 'бул. Витоша №46',
        'addressAdditions' => 'Звънецът не работи',
        'officeId' => '15',
        'firstName' => 'Иван',
        'lastName' => 'Петров',
        'phone' => '0883456789',
        'phBarangay' => '',
        'companyName' => 'My company',
        'companyVat' => '1234567',
    ];
    public const DS_ORDER_PRODUCTS = [
        [
            'productId' => self::LG_ALI_EXPRESS_PRODUCT_ID,
            'name' => 'LG',
            'quantity' => 2,
        ],
        [
            'productId' => self::HISENSE_ALI_EXPRESS_PRODUCT_ID,
            'name' => 'Hisense',
            'quantity' => 1,
        ],
    ];
}
