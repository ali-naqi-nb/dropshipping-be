<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\App;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AppResponseSchema',
    required: ['appId', 'config'],
    properties: [
        new OA\Property(property: 'appId', ref: '#/components/schemas/AppIdSchema'),
        new OA\Property(
            property: 'config',
            required: ['isActive', 'isInstalled'],
            oneOf: [
                new OA\Schema(
                    required: ['isInstalled', 'isActive'],
                    properties: [
                        new OA\Property(property: 'isInstalled', type: 'boolean'),
                        new OA\Property(property: 'isActive', type: 'boolean'),
                    ],
                ),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'isInstalled', type: 'boolean'),
                        new OA\Property(property: 'isActive', type: 'boolean'),
                        new OA\Property(property: 'clientId', type: 'integer'),
                    ],
                ),
            ],
        ),
    ],
)]
final class AppResponseSchema
{
    public const EXAMPLE_ALI_EXPRESS = [
        'appId' => AppIdSchema::ALI_EXPRESS,
        'config' => [
            'isActive' => true,
            'isInstalled' => true,
            'clientId' => 13344,
        ],
    ];
}
