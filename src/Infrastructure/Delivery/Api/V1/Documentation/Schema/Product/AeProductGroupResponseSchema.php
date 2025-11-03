<?php

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\Product;

use App\Tests\Shared\Factory\AeProductImportFactory;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AeProductGroupResponseSchema',
    required: [
        'id', 'aeProductId', 'progressStep', 'totalSteps',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'aeProductId', type: 'integer'),
        new OA\Property(property: 'progressStep', type: 'integer'),
        new OA\Property(property: 'totalSteps', type: 'integer'),
    ]
)]
final class AeProductGroupResponseSchema
{
    public const EXAMPLE_DEFAULT = [
        'id' => AeProductImportFactory::IMPORT_ID,
        'aeProductId' => AeProductImportFactory::AE_PRODUCT_ID,
        'progressStep' => AeProductImportFactory::IMPORT_COMPLETED_STEP,
        'totalSteps' => AeProductImportFactory::IMPORT_TOTAL_STEPS,
    ];
}
