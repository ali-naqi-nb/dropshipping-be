<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Parameter;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: '_locale',
    name: '_locale',
    description: 'Locale',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'string',
        default: 'en_US',
        enum: [
            'en_US',
            'bg_BG',
            'ro_RO',
            'el_GR',
            'de_DE',
            'fr_FR',
            'hu_HU',
            'pl_PL',
        ]
    )
)]
final class LocaleParameter
{
}
