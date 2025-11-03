<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Tenant\TenantDeleted;

final class TenantDeletedFactory
{
    public static function getTenantDeleted(): TenantDeleted
    {
        return new TenantDeleted(TenantFactory::TENANT_FOR_DELETE_ID);
    }
}
