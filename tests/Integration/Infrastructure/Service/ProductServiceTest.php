<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Service;

use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Service\ProductService;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Shared\Factory\RpcResultFactory;

final class ProductServiceTest extends IntegrationTestCase
{
    private ProductService $productService;

    private const COMMAND_DS_PRODUCT_TYPE_IMPORT = 'dsProductTypeImport';
    private const COMMAND_DS_ATTRIBUTE_IMPORT = 'dsAttributesImport';
    private const COMMAND_DS_PRODUCT_GROUP_IMPORT = 'dsProductGroupImport';
    private const COMMAND_DS_PRODUCT_IMAGES_UPDATE = 'dsProductImagesUpdate';

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ProductService $productService */
        $productService = $this->getContainer()->get(ProductService::class);
        $this->productService = $productService;
    }

    public function testSendDsProductTypeImport(): void
    {
        $result = [
            'productTypeName' => AeProductImportProductFactory::PRODUCT_TYPE_NAME,
            'dsProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'dsProvider' => DsProviderFactory::ALI_EXPRESS,
        ];

        $this->mockRpcResponse(
            function (RpcCommand $command) {
                if ($command->getCommand() !== 'products.'.self::COMMAND_DS_PRODUCT_TYPE_IMPORT) {
                    return false;
                }

                return true;
            },
            RpcResultFactory::getRpcCommandResult(result: $result),
        );

        $response = $this->productService->sendDsProductTypeImport(
            AeProductImportProductFactory::PRODUCT_TYPE_NAME,
            (string) AeProductImportProductFactory::AE_PRODUCT_ID
        );

        $this->assertIsArray($response);
        $this->assertSame($result, $response);
    }

    public function testSendDsAttributeImport(): void
    {
        $result = [
            'productTypeId' => ProductFactory::PRODUCT_TYPE_ID,
            'dsProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'dsProvider' => DsProviderFactory::ALI_EXPRESS,
            'attributes' => AeProductImportFactory::ATTRIBUTES,
            'status' => 'ack',
        ];

        $this->mockRpcResponse(
            function (RpcCommand $command) {
                if ($command->getCommand() !== 'products.'.self::COMMAND_DS_ATTRIBUTE_IMPORT) {
                    return false;
                }

                return true;
            },
            RpcResultFactory::getRpcCommandResult(result: $result),
        );

        $response = $this->productService->sendDsAttributeImport(
            ProductFactory::PRODUCT_TYPE_ID,
            (string) AeProductImportProductFactory::AE_PRODUCT_ID,
            AeProductImportFactory::ATTRIBUTES
        );

        $this->assertIsArray($response);
        $this->assertSame($result, $response);
    }

    public function testSendDsProductGroupImport(): void
    {
        $productsArr = [
            [
                'dsVariantId' => '1211212122',
                'dsProvider' => DsProviderFactory::ALI_EXPRESS,
                'name' => AeProductImportProductFactory::AE_PRODUCT_NAME,
            ],
        ];

        $result = [
            'dsProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'dsProvider' => DsProviderFactory::ALI_EXPRESS,
            'products' => $productsArr,
            'status' => 'ack',
        ];

        $this->mockRpcResponse(
            function (RpcCommand $command) {
                if ($command->getCommand() !== 'products.'.self::COMMAND_DS_PRODUCT_GROUP_IMPORT) {
                    return false;
                }

                return true;
            },
            RpcResultFactory::getRpcCommandResult(result: $result),
        );

        $response = $this->productService->sendDsProductGroupImport(
            ProductFactory::PRODUCT_TYPE_ID,
            $productsArr
        );

        $this->assertIsArray($response);
        $this->assertSame($result, $response);
    }

    public function testSendDsProductImagesUpdate(): void
    {
        $productsArr = [
            [
                'productId' => AeProductImportProductFactory::NB_PRODUCT_ID,
                'images' => [AeProductImportProductFactory::AE_IMAGE_URL],
            ],
        ];

        $result = [
            'dsProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'dsProvider' => DsProviderFactory::ALI_EXPRESS,
            'products' => $productsArr,
            'status' => 'ack',
        ];

        $this->mockRpcResponse(
            function (RpcCommand $command) {
                if ($command->getCommand() !== 'products.'.self::COMMAND_DS_PRODUCT_IMAGES_UPDATE) {
                    return false;
                }

                return true;
            },
            RpcResultFactory::getRpcCommandResult(result: $result),
        );

        $response = $this->productService->sendDsProductImagesUpdate(
            AeProductImportProductFactory::AE_PRODUCT_ID,
            $productsArr
        );

        $this->assertIsArray($response);
        $this->assertSame($result, $response);
    }
}
