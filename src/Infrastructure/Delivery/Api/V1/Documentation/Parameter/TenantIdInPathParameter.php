<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Parameter;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'tenantIdInPath',
    name: 'tenantId',
    description: 'Tenant id',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
final class TenantIdInPathParameter
{
}
