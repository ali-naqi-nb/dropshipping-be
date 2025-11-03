<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1\Documentation\Parameter;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'tenantId',
    name: 'x-tenant-id',
    description: 'Tenant id',
    in: 'header',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
final class TenantParameter
{
}
