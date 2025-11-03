<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Tenant\TenantStatusUpdated;

final class TenantStatusUpdatedFactory
{
    public const TENANT_ID = 'ad4f3865-5061-4b45-906c-562d37ac0830';

    public const NON_EXISTING_TENANT_ID = '9d9383f3-b2eb-46a9-b336-166cbb4fb000';
    public const DEFAULT_STATUS = 'test';

    public static function getStatusUpdated(
        string $id = self::TENANT_ID,
        string $status = self::DEFAULT_STATUS,
    ): TenantStatusUpdated {
        return new TenantStatusUpdated($id, $status);
    }

    public static function getNonExistingTenantConfigUpdated(
        string $id = self::NON_EXISTING_TENANT_ID,
        string $status = self::DEFAULT_STATUS,
    ): TenantStatusUpdated {
        return new TenantStatusUpdated($id, $status);
    }
}
