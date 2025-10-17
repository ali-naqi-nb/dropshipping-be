<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Parameter;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    name: 'id',
    description: 'Id',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
final class IdParameter
{
}
