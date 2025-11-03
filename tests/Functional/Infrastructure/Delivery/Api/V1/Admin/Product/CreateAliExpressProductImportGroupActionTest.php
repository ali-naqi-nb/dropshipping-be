<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\Product;

use App\Infrastructure\Rpc\Client\RpcCommandClientInterface;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class CreateAliExpressProductImportGroupActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/aliexpress-product-group';
    protected const METHOD = 'POST';

    private const REQUIRED_DATA = [
        'products' => [
            [
                'aeProductId' => AeProductImportProductFactory::NEW_AE_PRODUCT_ID,
                'aeSkuId' => AeProductImportProductFactory::AE_SKU_ID,
                'name' => AeProductImportProductFactory::NEW_AE_PRODUCT_NAME,
                'description' => AeProductImportProductFactory::NEW_AE_PRODUCT_DESCRIPTION,
                'sku' => AeProductImportProductFactory::AE_IMPORT_SKU_CODE,
                'price' => AeProductImportProductFactory::AE_SKU_PRICE,
                'mainCategoryId' => ProductFactory::CATEGORY_ID_FIRST,
                'additionalCategories' => [ProductFactory::CATEGORY_ID_SECOND],
                'stock' => AeProductImportProductFactory::AE_PRODUCT_STOCK,
                'barcode' => AeProductImportProductFactory::NEW_AE_PRODUCT_BARCODE,
                'weight' => AeProductImportProductFactory::AE_PRODUCT_WEIGHT,
                'length' => AeProductImportProductFactory::AE_PRODUCT_LENGTH,
                'width' => AeProductImportProductFactory::AE_IMPORT_PACKAGE_WIDTH,
                'height' => AeProductImportProductFactory::AE_PRODUCT_HEIGHT,
                'costPerItem' => AeProductImportProductFactory::AE_SKU_PRICE,
                'productTypeName' => AeProductImportProductFactory::PRODUCT_TYPE_NAME,
                'attributes' => AeProductImportFactory::ATTRIBUTES,
                'images' => [AeProductImportProductFactory::AE_IMAGE_URL],
                'shippingOption' => [
                    'code' => 'CAINIAO_STANDARD',
                    'shipsFrom' => 'CN',
                    'minDeliveryDays' => 15,
                    'maxDeliveryDays' => 30,
                    'shippingFeePrice' => 500,
                    'shippingFeeCurrency' => 'USD',
                    'isFreeShipping' => false,
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUserHeaders();

        $mockRpcClient = $this->createMock(RpcCommandClientInterface::class);
        $mockRpcClient->method('call')
            ->willReturn(new RpcResult(
                executedAt: time(),
                commandId: 'test-command-id',
                status: RpcResultStatus::SUCCESS,
                result: ['success' => true, 'productTypeId' => 'test-id']
            ));

        self::getContainer()->set(RpcCommandClientInterface::class, $mockRpcClient);
    }

    public function testCreateAliExpressProductGroupReturns201(): void
    {
        $this->makeTenantRequest(method: self::METHOD, data: self::REQUIRED_DATA);
        $this->assertResponseSuccess([
            'id' => '@string@',
            'aeProductId' => AeProductImportProductFactory::NEW_AE_PRODUCT_ID,
            'progressStep' => '@integer@',
            'totalSteps' => '@integer@',
        ], 201);
    }

    public function testCreateAliExpressProductGroupWithExistingProductReturns201(): void
    {
        $data = self::REQUIRED_DATA;
        $data['products'][0]['aeProductId'] = AeProductImportProductFactory::AE_PRODUCT_ID;

        $this->makeTenantRequest(method: self::METHOD, data: $data);
        $this->assertResponseErrors(['common' => 'Aliexpress Product Group already exist.'], 422);
    }

    /**
     * @dataProvider provideInvalidData400
     */
    public function testCreateAliExpressProductGroupReturns400(array $data, array $errors): void
    {
        $this->makeTenantRequest(method: self::METHOD, data: $data);
        $this->assertResponseErrors($errors);
    }

    public function provideInvalidData400(): array
    {
        return [
            'empty products' => [
                ['products' => []],
                ['products' => 'This collection should contain 1 element or more.'],
            ],
            'missing required fields' => [
                ['products' => [['aeProductId' => 123]]],
                [
                    'products.0.aeSkuId' => 'This field is missing.',
                    'products.0.name' => 'This field is missing.',
                    'products.0.description' => 'This field is missing.',
                    'products.0.sku' => 'This field is missing.',
                    'products.0.price' => 'This field is missing.',
                    'products.0.mainCategoryId' => 'This field is missing.',
                    'products.0.additionalCategories' => 'This field is missing.',
                    'products.0.stock' => 'This field is missing.',
                    'products.0.barcode' => 'This field is missing.',
                    'products.0.weight' => 'This field is missing.',
                    'products.0.length' => 'This field is missing.',
                    'products.0.width' => 'This field is missing.',
                    'products.0.height' => 'This field is missing.',
                    'products.0.costPerItem' => 'This field is missing.',
                    'products.0.productTypeName' => 'This field is missing.',
                    'products.0.attributes' => 'This field is missing.',
                    'products.0.images' => 'This field is missing.',
                    'products.0.shippingOption' => 'This field is missing.',
                ],
            ],
        ];
    }
}
