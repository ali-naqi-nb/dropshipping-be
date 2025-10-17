<?php

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Tenant\TenantConfigUpdated;

class TenantConfigUpdatedFactory
{
    public const TENANT_ID = 'ad4f3865-5061-4b45-906c-562d37ac0830';
    public const NON_EXISTING_TENANT_ID = '9d9383f3-b2eb-46a9-b336-166cbb4fb000';
    public const DEFAULT_LANGUAGE = 'bg_BG';
    public const DEFAULT_CURRENCY = 'BGN';

    public static function getConfigUpdated(
        string $id = self::TENANT_ID,
        string $defaultLanguage = self::DEFAULT_LANGUAGE,
        string $defaultCurrency = self::DEFAULT_CURRENCY
    ): TenantConfigUpdated {
        return new TenantConfigUpdated($id, $defaultLanguage, $defaultCurrency);
    }

    public static function getNonExistingTenantConfigUpdated(
        string $id = self::NON_EXISTING_TENANT_ID,
        string $defaultLanguage = self::DEFAULT_LANGUAGE,
        string $defaultCurrency = self::DEFAULT_CURRENCY
    ): TenantConfigUpdated {
        return new TenantConfigUpdated($id, $defaultLanguage, $defaultCurrency);
    }
}
