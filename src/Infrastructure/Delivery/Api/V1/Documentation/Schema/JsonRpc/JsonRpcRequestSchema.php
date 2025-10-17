<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\JsonRpc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'JsonRpcRequestSchema',
    required: ['jsonrpc', 'method'],
    properties: [
        new OA\Property(property: 'jsonrpc', type: 'string', default: '2.0'),
        new OA\Property(property: 'method', type: 'string'),
        new OA\Property(property: 'params', type: 'array', items: new OA\Items()),
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    ],
)]
final class JsonRpcRequestSchema
{
    public const EXAMPLE_DS_PRODUCT_TYPE_IMPORTED = [
        'jsonrpc' => '2.0',
        'method' => 'dsProductTypeImported',
        'tenantId' => '2de469c4-d761-4840-a6d2-7d12dc6b27ea',
        'params' => [
            [
                'productTypeName' => 'Cloths',
                'productTypeId' => 'fde469c4-d761-4840-a6d2-7d12dc6b27ea',
                'dsProductId' => '1005007433426570',
                'dsProvider' => 'ali-express',
                'status' => 'success',
            ],
        ],
    ];
    public const EXAMPLE_DS_PRODUCT_IMAGES_IMPORTED = [
        'jsonrpc' => '2.0',
        'method' => 'dsProductImagesImported',
        'tenantId' => '2de469c4-d761-4840-a6d2-7d12dc6b27ea',
        'params' => [
            [
                'dsProductId' => '1005007433426570',
                'dsVariantId' => '12000042556247161',
                'dsProvider' => 'ali-express',
                'images' => ['https://example.com/image.png'],
                'status' => 'success',
            ],
        ],
        'id' => 'cff92407-d772-447f-af6b-d53722361948',
    ];
    public const EXAMPLE_DS_PRODUCT_GROUP_IMPORTED = [
        'jsonrpc' => '2.0',
        'method' => 'dsProductGroupImported',
        'tenantId' => '2de469c4-d761-4840-a6d2-7d12dc6b27ea',
        'params' => [
            [
                'dsProductId' => '1005007433426570',
                'dsProvider' => 'ali-express',
                'products' => [
                    [
                        'dsVariantId' => '1005007445321570',
                        'productId' => 'dde469c4-d731-4840-a6d2-7d12dc6b27ea',
                        'name' => 'some name 1',
                    ],
                ],
                'status' => 'success',
            ],
        ],
        'id' => 'cff92407-d772-447f-af6b-d53722361948',
    ];
    public const EXAMPLE_DS_ATTRIBUTES_IMPORTED = [
        'jsonrpc' => '2.0',
        'method' => 'dsAttributesImported',
        'tenantId' => '2de469c4-d761-4840-a6d2-7d12dc6b27ea',
        'params' => [
            [
                'productTypeId' => '9005007433426140',
                'dsProductId' => '1005007433426570',
                'dsProvider' => 'ali-express',
                'attributes' => [
                    [
                        'attributeId' => 'fde469c4-d761-4840-a6d2-7d12dc6b27ea',
                        'name' => 'color',
                        'type' => 'dropdown',
                        'value' => 'blue',
                    ],
                ],
                'status' => 'success',
            ],
        ],
        'id' => 'cff92407-d772-447f-af6b-d53722361948',
    ];
    public const EXAMPLE_DS_PRODUCT_IMAGES_UPDATED = [
        'jsonrpc' => '2.0',
        'method' => 'dsProductImagesUpdated',
        'tenantId' => '2de469c4-d761-4840-a6d2-7d12dc6b27ea',
        'params' => [
            [
                'dsProductId' => '1005007433426570',
                'dsVariantId' => '12000042556247161',
                'dsProvider' => 'ali-express',
                'products' => [
                    [
                        'dsVariantId' => '1005007445321570',
                        'productId' => 'dde469c4-d731-4840-a6d2-7d12dc6b27ea',
                        'name' => 'some name 1',
                    ],
                ],
                'status' => 'success',
            ],
        ],
        'id' => 'cff92407-d772-447f-af6b-d53722361948',
    ];
}
