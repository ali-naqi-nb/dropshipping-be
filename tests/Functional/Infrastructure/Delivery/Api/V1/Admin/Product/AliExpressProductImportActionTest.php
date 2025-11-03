<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\Product;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class AliExpressProductImportActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/aliexpress-product-import';
    protected const METHOD = 'POST';

    private const REQUIRED_DATA = [
        'aeProductUrl' => Factory::AE_PRODUCT_URL,
        'aeProductShipsTo' => Factory::AE_PRODUCT_SHIPS_TO,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUserHeaders();
        $this->setAeAccessToken();
    }

    public function testAliExpressProductImportReturns201(): void
    {
        $this->makeTenantRequest(method: self::METHOD, data: self::REQUIRED_DATA);
        $this->assertResponseSuccess(['items' => Factory::RESPONSE_ITEMS_PATTERN], 201);
    }

    /**
     * @dataProvider provideInvalidData400
     */
    public function testAliExpressProductImportReturns400(array $data, array $errors): void
    {
        $this->makeTenantRequest(method: self::METHOD, data: $data);
        $this->assertResponseErrors($errors);
    }

    /**
     * @dataProvider provideInvalidData422
     */
    public function testAliExpressProductImportReturns422(array $data, array $errors): void
    {
        $this->makeTenantRequest(method: self::METHOD, data: $data);
        $this->assertResponseErrors($errors, 422);
    }

    public function provideInvalidData400(): array
    {
        return [
            'empty' => [
                [
                    'aeProductUrl' => '',
                    'aeProductShipsTo' => '',
                ],
                [
                    'aeProductUrl' => 'This value should not be blank.',
                    'aeProductShipsTo' => 'This value should not be blank.',
                ],
            ],
            'invalidUrl' => [
                array_merge(self::REQUIRED_DATA, [
                    'aeProductUrl' => 'https://www.google.com',
                ]),
                [
                    'aeProductUrl' => 'Invalid AliExpress product URL.',
                ],
            ],
            'invalidCountry' => [
                array_merge(self::REQUIRED_DATA, [
                    'aeProductShipsTo' => '77',
                ]),
                [
                    'aeProductShipsTo' => 'This value is not a valid country.',
                ],
            ],
        ];
    }

    public function provideInvalidData422(): array
    {
        return [
            'failedFetchingAeDetails' => [
                array_merge(self::REQUIRED_DATA, [
                    'aeProductUrl' => Factory::AE_PRODUCT_URL_TEST_ERROR,
                ]),
                [
                    'common' => 'Failed to get product information from AliExpress.',
                ],
            ],
        ];
    }
}
