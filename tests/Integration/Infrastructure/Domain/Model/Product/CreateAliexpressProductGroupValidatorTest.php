<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Product;

use App\Domain\Model\Product\CreateAliexpressProductGroupValidatorInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Trait\Assertions\ValidationAssertionsTrait;

final class CreateAliexpressProductGroupValidatorTest extends IntegrationTestCase
{
    use ValidationAssertionsTrait;

    private CreateAliexpressProductGroupValidatorInterface $validator;

    private const VALID_PRODUCT = [
        'aeProductId' => 123,
        'aeSkuId' => 456,
        'name' => 'Test Product',
        'description' => 'Test product description',
        'sku' => 'TEST-SKU-001',
        'price' => 2999,
        'stock' => 10,
        'mainCategoryId' => '550e8400-e29b-41d4-a716-446655440000',
        'additionalCategories' => ['550e8400-e29b-41d4-a716-446655440001'],
        'barcode' => '1234567890',
        'weight' => 500,
        'length' => 20,
        'height' => 10,
        'width' => 15,
        'costPerItem' => 1500,
        'productTypeName' => 'Electronics',
        'attributes' => [
            [
                'name' => 'Color',
                'type' => 'string',
                'value' => 'Red',
            ],
        ],
        'images' => [
            'https://example.com/image1.jpg',
            'https://example.com/image2.jpg',
        ],
        'shippingOption' => [
            'code' => 'CAINIAO_STANDARD',
            'shipsFrom' => 'CN',
            'minDeliveryDays' => 15,
            'maxDeliveryDays' => 30,
            'shippingFeePrice' => 500,
            'shippingFeeCurrency' => 'USD',
            'isFreeShipping' => false,
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CreateAliexpressProductGroupValidatorInterface $validator */
        $validator = self::getContainer()->get(CreateAliexpressProductGroupValidatorInterface::class);
        $this->validator = $validator;
    }

    /**
     * @dataProvider provideValidData
     */
    public function testValidateWithValidData(array $data): void
    {
        $result = $this->validator->validate($data);
        $this->assertNoErrors($result);
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testValidateWithInvalidData(array $data, array $expectedErrors): void
    {
        $result = $this->validator->validate($data);
        $this->assertErrors($expectedErrors, $result);
    }

    public function provideValidData(): array
    {
        return [
            'valid single product' => [
                [
                    'products' => [self::VALID_PRODUCT],
                ],
            ],
            'valid multiple products' => [
                [
                    'products' => [self::VALID_PRODUCT, self::VALID_PRODUCT],
                ],
            ],
            'valid product with zero values' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'aeProductId' => 0,
                            'aeSkuId' => 0,
                            'price' => 0,
                            'stock' => 0,
                            'weight' => 0,
                            'length' => 0,
                            'height' => 0,
                            'width' => 0,
                            'costPerItem' => 0,
                        ]),
                    ],
                ],
            ],
            'valid product without additional categories' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'additionalCategories' => [],
                        ]),
                    ],
                ],
            ],
            'valid product with free shipping' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => [
                                'code' => 'FREE_SHIPPING',
                                'shipsFrom' => 'US',
                                'minDeliveryDays' => 5,
                                'maxDeliveryDays' => 10,
                                'shippingFeePrice' => 0,
                                'shippingFeeCurrency' => 'USD',
                                'isFreeShipping' => true,
                            ],
                        ]),
                    ],
                ],
            ],
            'valid product with zero delivery days' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => [
                                'code' => 'EXPRESS',
                                'shipsFrom' => 'CN',
                                'minDeliveryDays' => 0,
                                'maxDeliveryDays' => 0,
                                'shippingFeePrice' => 0,
                                'shippingFeeCurrency' => 'EUR',
                                'isFreeShipping' => false,
                            ],
                        ]),
                    ],
                ],
            ],
        ];
    }

    public function provideInvalidData(): array
    {
        return [
            'missing products field' => [
                [],
                [
                    ['path' => 'products', 'message' => 'This field is missing.'],
                ],
            ],
            'empty products string' => [
                ['products' => ''],
                [
                    ['path' => 'products', 'message' => 'This value should be of type array|\Countable.'],
                    ['path' => 'products', 'message' => 'This value should be of type iterable.'],
                ],
            ],
            'empty products array' => [
                ['products' => []],
                [
                    ['path' => 'products', 'message' => 'This collection should contain 1 element or more.'],
                ],
            ],
            'negative aeProductId' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['aeProductId' => -1]),
                    ],
                ],
                [
                    ['path' => 'products.0.aeProductId', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'negative aeSkuId' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['aeSkuId' => -1]),
                    ],
                ],
                [
                    ['path' => 'products.0.aeSkuId', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'missing name' => [
                [
                    'products' => [
                        array_diff_key(self::VALID_PRODUCT, array_flip(['name'])),
                    ],
                ],
                [
                    ['path' => 'products.0.name', 'message' => 'This field is missing.'],
                ],
            ],
            'blank name' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['name' => '']),
                    ],
                ],
                [
                    ['path' => 'products.0.name', 'message' => 'This value should not be blank.'],
                ],
            ],
            'non-string description' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['description' => 123]),
                    ],
                ],
                [
                    ['path' => 'products.0.description', 'message' => 'This value should be of type string.'],
                ],
            ],
            'non-string sku' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['sku' => 123]),
                    ],
                ],
                [
                    ['path' => 'products.0.sku', 'message' => 'This value should be of type string.'],
                ],
            ],
            'non-integer price' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['price' => '2999']),
                    ],
                ],
                [
                    ['path' => 'products.0.price', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative price' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['price' => -100]),
                    ],
                ],
                [
                    ['path' => 'products.0.price', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'non-integer stock' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['stock' => '10']),
                    ],
                ],
                [
                    ['path' => 'products.0.stock', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative stock' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['stock' => -1]),
                    ],
                ],
                [
                    ['path' => 'products.0.stock', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'missing mainCategoryId' => [
                [
                    'products' => [
                        array_diff_key(self::VALID_PRODUCT, array_flip(['mainCategoryId'])),
                    ],
                ],
                [
                    ['path' => 'products.0.mainCategoryId', 'message' => 'This field is missing.'],
                ],
            ],
            'blank mainCategoryId' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['mainCategoryId' => '']),
                    ],
                ],
                [
                    ['path' => 'products.0.mainCategoryId', 'message' => 'This value should not be blank.'],
                ],
            ],
            'invalid uuid mainCategoryId' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['mainCategoryId' => 'invalid-uuid']),
                    ],
                ],
                [
                    ['path' => 'products.0.mainCategoryId', 'message' => 'This value is not a valid UUID.'],
                ],
            ],
            'invalid uuid in additionalCategories' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['additionalCategories' => ['invalid-uuid']]),
                    ],
                ],
                [
                    ['path' => 'products.0.additionalCategories.0', 'message' => 'This value is not a valid UUID.'],
                ],
            ],
            'non-string barcode' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['barcode' => 123]),
                    ],
                ],
                [
                    ['path' => 'products.0.barcode', 'message' => 'This value should be of type string.'],
                ],
            ],
            'non-integer weight' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['weight' => '500']),
                    ],
                ],
                [
                    ['path' => 'products.0.weight', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative weight' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['weight' => -100]),
                    ],
                ],
                [
                    ['path' => 'products.0.weight', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'non-integer length' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['length' => '20']),
                    ],
                ],
                [
                    ['path' => 'products.0.length', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative length' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['length' => -10]),
                    ],
                ],
                [
                    ['path' => 'products.0.length', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'non-integer height' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['height' => '10']),
                    ],
                ],
                [
                    ['path' => 'products.0.height', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative height' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['height' => -5]),
                    ],
                ],
                [
                    ['path' => 'products.0.height', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'non-integer width' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['width' => '15']),
                    ],
                ],
                [
                    ['path' => 'products.0.width', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative width' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['width' => -8]),
                    ],
                ],
                [
                    ['path' => 'products.0.width', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'non-integer costPerItem' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['costPerItem' => '1500']),
                    ],
                ],
                [
                    ['path' => 'products.0.costPerItem', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative costPerItem' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['costPerItem' => -100]),
                    ],
                ],
                [
                    ['path' => 'products.0.costPerItem', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'missing productTypeName' => [
                [
                    'products' => [
                        array_diff_key(self::VALID_PRODUCT, array_flip(['productTypeName'])),
                    ],
                ],
                [
                    ['path' => 'products.0.productTypeName', 'message' => 'This field is missing.'],
                ],
            ],
            'blank productTypeName' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, ['productTypeName' => '']),
                    ],
                ],
                [
                    ['path' => 'products.0.productTypeName', 'message' => 'This value should not be blank.'],
                ],
            ],
            'invalid attribute missing name' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'attributes' => [
                                [
                                    'type' => 'string',
                                    'value' => 'Red',
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.attributes.0.name', 'message' => 'This field is missing.'],
                ],
            ],
            'invalid attribute blank name' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'attributes' => [
                                [
                                    'name' => '',
                                    'type' => 'string',
                                    'value' => 'Red',
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.attributes.0.name', 'message' => 'This value should not be blank.'],
                ],
            ],
            'invalid attribute missing type' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'attributes' => [
                                [
                                    'name' => 'Color',
                                    'value' => 'Red',
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.attributes.0.type', 'message' => 'This field is missing.'],
                ],
            ],
            'invalid attribute blank type' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'attributes' => [
                                [
                                    'name' => 'Color',
                                    'type' => '',
                                    'value' => 'Red',
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.attributes.0.type', 'message' => 'This value should not be blank.'],
                ],
            ],
            'invalid attribute missing value' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'attributes' => [
                                [
                                    'name' => 'Color',
                                    'type' => 'string',
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.attributes.0.value', 'message' => 'This field is missing.'],
                ],
            ],
            'invalid attribute blank value' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'attributes' => [
                                [
                                    'name' => 'Color',
                                    'type' => 'string',
                                    'value' => '',
                                ],
                            ],
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.attributes.0.value', 'message' => 'This value should not be blank.'],
                ],
            ],
            'blank image url' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'images' => [''],
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.images.0', 'message' => 'This value should not be blank.'],
                ],
            ],
            'missing shippingOption' => [
                [
                    'products' => [
                        array_diff_key(self::VALID_PRODUCT, array_flip(['shippingOption'])),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption', 'message' => 'This field is missing.'],
                ],
            ],
            'missing shippingOption code' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_diff_key(self::VALID_PRODUCT['shippingOption'], array_flip(['code'])),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.code', 'message' => 'This field is missing.'],
                ],
            ],
            'blank shippingOption code' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['code' => '']),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.code', 'message' => 'This value should not be blank.'],
                ],
            ],
            'missing shippingOption shipsFrom' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_diff_key(self::VALID_PRODUCT['shippingOption'], array_flip(['shipsFrom'])),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.shipsFrom', 'message' => 'This field is missing.'],
                ],
            ],
            'blank shippingOption shipsFrom' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['shipsFrom' => '']),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.shipsFrom', 'message' => 'This value should not be blank.'],
                ],
            ],
            'non-integer minDeliveryDays' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['minDeliveryDays' => '15']),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.minDeliveryDays', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative minDeliveryDays' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['minDeliveryDays' => -5]),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.minDeliveryDays', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'non-integer maxDeliveryDays' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['maxDeliveryDays' => '30']),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.maxDeliveryDays', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative maxDeliveryDays' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['maxDeliveryDays' => -10]),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.maxDeliveryDays', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'non-integer shippingFeePrice' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['shippingFeePrice' => '500']),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.shippingFeePrice', 'message' => 'This value should be of type integer.'],
                ],
            ],
            'negative shippingFeePrice' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['shippingFeePrice' => -100]),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.shippingFeePrice', 'message' => 'This value should be either positive or zero.'],
                ],
            ],
            'missing shippingFeeCurrency' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_diff_key(self::VALID_PRODUCT['shippingOption'], array_flip(['shippingFeeCurrency'])),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.shippingFeeCurrency', 'message' => 'This field is missing.'],
                ],
            ],
            'blank shippingFeeCurrency' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['shippingFeeCurrency' => '']),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.shippingFeeCurrency', 'message' => 'This value should not be blank.'],
                ],
            ],
            'non-boolean isFreeShipping' => [
                [
                    'products' => [
                        array_merge(self::VALID_PRODUCT, [
                            'shippingOption' => array_merge(self::VALID_PRODUCT['shippingOption'], ['isFreeShipping' => 'true']),
                        ]),
                    ],
                ],
                [
                    ['path' => 'products.0.shippingOption.isFreeShipping', 'message' => 'This value should be of type bool.'],
                ],
            ],
        ];
    }
}
