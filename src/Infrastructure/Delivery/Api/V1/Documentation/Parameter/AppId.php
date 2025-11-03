<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Parameter;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    name: 'appId',
    description: 'App id',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', enum: ['ali-express'])
)]
final class AppId
{
}
