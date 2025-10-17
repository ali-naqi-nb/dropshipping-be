<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Schema\App;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AppIdSchema', type: 'string', enum: ['ali-express'],
)]
final class AppIdSchema
{
    public const ALI_EXPRESS = 'ali-express';
}
