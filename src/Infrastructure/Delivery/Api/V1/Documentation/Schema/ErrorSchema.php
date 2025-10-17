<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorSchema',
    required: ['errors'],
    properties: [
        new OA\Property(
            property: 'errors',
            additionalProperties: new OA\AdditionalProperties(
                description: 'At least one property will be present',
                type: 'string',
            ),
        ),
    ],
)]
final class ErrorSchema
{
}
