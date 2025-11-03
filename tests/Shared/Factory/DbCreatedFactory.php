<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Tenant\DropshippingDbCreated;
use App\Domain\Model\Tenant\ShopStatus;

final class DbCreatedFactory
{
    public const NON_EXISTING_TENANT_ID = '9d9383f3-b2eb-46a9-b336-166cbb4fb000';
    public const NON_EXISTING_DOMAIN = 'my-new-shop.nextbasket.test';

    public const TENANT_ID = 'ad4f3865-5061-4b45-906c-562d37ac0830';
    public const COMPANY_ID = '9c4bb7f8-59df-48b1-9f65-ec143ef4652c';
    public const DOMAIN = 'my-test-shop.nextbasket.test';
    public const DEFAULT_CURRENCY = 'BGN';
    public const DEFAULT_LANGUAGE = 'bg_BG';

    public static function getDbCreated(
        string $id = self::TENANT_ID,
        string $companyId = self::COMPANY_ID,
        string $domain = self::DOMAIN,
        ?string $config = null,
        string $defaultCurrency = self::DEFAULT_CURRENCY,
        string $defaultLanguage = self::DEFAULT_LANGUAGE,
        ShopStatus $status = ShopStatus::Test,
        bool $dbCreated = false
    ): DropshippingDbCreated {
        $config = $config ?? self::getConfig();

        return new DropshippingDbCreated(
            tenantId: $id,
            defaultCurrency: $defaultCurrency,
            defaultLanguage: $defaultLanguage,
            companyId: $companyId,
            domain: $domain,
            config: $config,
            status: $status->value,
            dbCreated: $dbCreated
        );
    }

    public static function getNonExistingDbCreated(
        string $id = self::NON_EXISTING_TENANT_ID,
        string $companyId = self::COMPANY_ID,
        string $domain = self::NON_EXISTING_DOMAIN,
        ?string $config = null,
        string $defaultCurrency = self::DEFAULT_CURRENCY,
        string $defaultLanguage = self::DEFAULT_LANGUAGE,
        ShopStatus $status = ShopStatus::Test,
    ): DropshippingDbCreated {
        $config = $config ?? self::getNonExistingConfig();

        return new DropshippingDbCreated(
            tenantId: $id,
            defaultCurrency: $defaultCurrency,
            defaultLanguage: $defaultLanguage,
            companyId: $companyId,
            domain: $domain,
            config: $config,
            status: $status->value,
        );
    }

    public static function getConfig(): string
    {
        $user = sprintf('%s165307955289cd1228', getenv('SERVICE_NAME'));
        $password = '691b2af4810d48af98e554dda0164d05';
        $database = sprintf('%s_9d9383f3b2eb46a9b336166cbb4fb000', getenv('SERVICE_NAME'));
        $dbHost = 'test-services-database';
        $dbPort = 3306;

        return sprintf(
            '%s|%s|%s|%s|%d',
            $user,
            $password,
            $database,
            $dbHost,
            $dbPort
        );
    }

    public static function getNonExistingConfig(): string
    {
        $user = sprintf('%s165307955289cd1228', getenv('SERVICE_NAME'));
        $password = '691b2af4810d48af98e554dda0164d05';
        $database = sprintf('%s_9d9383f3b2eb46a9b336166cbb4fb000', getenv('SERVICE_NAME'));
        $dbHost = 'test-services-database';
        $dbPort = 3306;

        return sprintf(
            '%s|%s|%s|%s|%d',
            $user,
            $password,
            $database,
            $dbHost,
            $dbPort
        );
    }
}
