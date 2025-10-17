<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Tenant\ShopStatus;
use App\Domain\Model\Tenant\Tenant;

final class TenantFactory
{
    public const NON_EXISTING_TENANT_ID = '9d9383f3-b2eb-46a9-b336-166cbb4fb000';
    public const TENANT_ID = 'ad4f3865-5061-4b45-906c-562d37ac0830';
    public const COMPANY_ID = '9c4bb7f8-59df-48b1-9f65-ec143ef4652c';
    public const LANGUAGE_EN = 'en_US';
    public const CURRENCY_EUR = 'EUR';
    public const DOMAIN = 'my-test-shop.nextbasket.test';
    public const DEFAULT_LANGUAGE = 'bg_BG';
    public const DEFAULT_CURRENCY = 'BGN';

    public const SECOND_TENANT_ID = 'a71f35dc-afbd-4902-b1b6-c26912786f19';
    public const SECOND_COMPANY_ID = '0b44c609-8901-4e46-b684-6b04e0b9cc49';
    public const SECOND_DOMAIN = 'second-my-test-shop.nextbasket.test';
    public const SECOND_CONFIG = 'foo';
    public const SECOND_LANGUAGE = 'bg_BG';
    public const SECOND_CURRENCY = 'USD';

    public const TENANT_FOR_DELETE_ID = '167794ae-e249-448f-a8a4-7c536a8719c0';
    public const TENANT_FOR_DELETE_COMPANY_ID = '64739949-9141-49b8-b644-905cf896cf95';
    public const TENANT_FOR_DELETE_DOMAIN = 'domain-for-delete.nextbasket.test';
    public const TENANT_FOR_DELETE_CONFIG = 'foo';
    public const TENANT_FOR_DELETE_LANGUAGE = 'bg_BG';
    public const TENANT_FOR_DELETE_CURRENCY = 'USD';

    public const DS_AUTHORISED_TENANT_ID = 'af085b5f-f47c-4f07-97c8-260908bfe135';
    public const DS_AUTHORISED_TENANT_COMPANY_ID = 'a49f0503-37fa-4549-86ec-c93e5643e624';
    public const DS_AUTHORISED_TENANT_DOMAIN = 'dropshipper-shop.nextbasket.test';
    public const DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID = '725145236';

    public const SECOND_DS_AUTHORISED_TENANT_ID_ = 'af185b5f-f47c-4f07-97c8-260908bfe135';
    public const SECOND_DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID = '725145235';

    public const NON_EXIST_ALIEXPRESS_SELLER_ID = '765195237';

    public const TENANT_STATUS_TEST = ShopStatus::Test;
    public const TENANT_STATUS_LIVE = ShopStatus::Live;
    public const TENANT_STATUS_TEST_EXPIRED = ShopStatus::TestExpired;
    public const TENANT_STATUS_SUSPENDED = ShopStatus::Suspended;

    public static function getTenant(
        bool $isAvailable = false,
        string $tenantId = self::TENANT_ID,
        string $companyId = self::COMPANY_ID,
        string $domain = self::DOMAIN,
        ?string $config = null,
        string $defaultLanguage = self::DEFAULT_LANGUAGE,
        string $defaultCurrency = self::DEFAULT_CURRENCY,
        ShopStatus $status = self::TENANT_STATUS_TEST,
    ): Tenant {
        $config = $config ?? self::getConfig();

        $tenant = new Tenant(
            id: $tenantId,
            companyId: $companyId,
            domain: $domain,
            dbConfig: $config,
            defaultLanguage: $defaultLanguage,
            defaultCurrency: $defaultCurrency,
            status: $status
        );

        if ($isAvailable) {
            $tenant->makeAvailable();
        }

        return $tenant;
    }

    public static function getNonExistingTenant(bool $isAvailable = false): Tenant
    {
        return self::getTenant(isAvailable: $isAvailable, tenantId: self::NON_EXISTING_TENANT_ID);
    }

    public static function getConfig(): string
    {
        return DbConfigFactory::getString(
            DbConfigFactory::getDbConfig(
                tenantId: self::TENANT_ID,
                user: sprintf('%s165307955289cc1129', getenv('SERVICE_NAME')),
                password: '691b2af4810d48af98e554dda0964d08',
                database: sprintf('%s_ad4f3865_5061_4b45_906c_562d37ac0830', getenv('SERVICE_NAME')),
                host: 'test-services-database',
                port: 3306,
            )
        );
    }
}
