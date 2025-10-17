<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Application\Service\ProductServiceInterface;
use App\Infrastructure\Rpc\Client\RpcCommandClientInterface;

final class ProductService implements ProductServiceInterface
{
    private const SERVICE = 'products';

    private const COMMAND_DS_PRODUCT_TYPE_IMPORT = 'dsProductTypeImport';
    private const COMMAND_DS_ATTRIBUTE_IMPORT = 'dsAttributesImport';
    private const COMMAND_DS_PRODUCT_GROUP_IMPORT = 'dsProductGroupImport';

    private const COMMAND_DS_PRODUCT_IMAGES_UPDATE = 'dsProductImagesUpdate';

    public function __construct(
        private readonly RpcCommandClientInterface $commandClient
    ) {
    }

    public function sendDsProductTypeImport(string $productTypeName, string $dsProductId, string $dsProvider = 'AliExpress'): array
    {
        $result = $this->commandClient->call(
            service: self::SERVICE,
            command: self::COMMAND_DS_PRODUCT_TYPE_IMPORT,
            arguments: [
                [
                    'productTypeName' => $productTypeName,
                    'dsProductId' => $dsProductId,
                    'dsProvider' => $dsProvider,
                ],
            ]
        );

        return $result->getResult();
    }

    public function sendDsAttributeImport(string $productTypeId, string $dsProductId, array $attributes, string $dsProvider = 'AliExpress'): array
    {
        $result = $this->commandClient->call(
            service: self::SERVICE,
            command: self::COMMAND_DS_ATTRIBUTE_IMPORT,
            arguments: [
                [
                    'productTypeId' => $productTypeId,
                    'dsProductId' => $dsProductId,
                    'dsProvider' => $dsProvider,
                    'attributes' => $attributes,
                ],
            ]
        );

        return $result->getResult();
    }

    public function sendDsProductGroupImport(string $dsProductId, array $products, string $dsProvider = 'AliExpress'): array
    {
        $result = $this->commandClient->call(
            service: self::SERVICE,
            command: self::COMMAND_DS_PRODUCT_GROUP_IMPORT,
            arguments: [
                [
                    'dsProductId' => $dsProductId,
                    'dsProvider' => $dsProvider,
                    'products' => $products,
                ],
            ]
        );

        return $result->getResult();
    }

    public function sendDsProductImagesUpdate(int|string $dsProductId, array $products, string $dsProvider = 'AliExpress'): array
    {
        $result = $this->commandClient->call(
            service: self::SERVICE,
            command: self::COMMAND_DS_PRODUCT_IMAGES_UPDATE,
            arguments: [
                [
                    'dsProductId' => $dsProductId,
                    'dsProvider' => $dsProvider,
                    'products' => $products,
                ],
            ]
        );

        return $result->getResult();
    }
}
