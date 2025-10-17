<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Tenant\DbConfig;

final class DbConfigFactory
{
    public const TENANT_ID = 'ad4f3865-5061-4b45-906c-562d37ac0830';
    public const PASSWORD = '691b2af4810d48af98e554dda0964d08';
    public const HOST = 'test-services-database';
    public const PORT = 3306;

    public static function getDbConfig(
        string $tenantId = self::TENANT_ID,
        ?string $user = null,
        string $password = self::PASSWORD,
        ?string $database = null,
        string $host = self::HOST,
        int $port = self::PORT,
    ): DbConfig {
        $user = $user ?? self::getUser();
        $database = $database ?? self::getDatabase();

        return new DbConfig($tenantId, $user, $password, $database, $host, $port);
    }

    public static function getUser(): string
    {
        return sprintf('%s165307955289cc1129', getenv('SERVICE_NAME'));
    }

    public static function getDatabase(): string
    {
        return sprintf('%s_ad4f3865_5061_4b45_906c_562d37ac0830', getenv('SERVICE_NAME'));
    }

    public static function getString(?DbConfig $config = null): string
    {
        $config = $config ?? self::getDbConfig();

        return sprintf(
            '%s|%s|%s|%s|%d',
            $config->getUser(),
            $config->getPassword(),
            $config->getDatabase(),
            $config->getDbHost(),
            $config->getDbPort()
        );
    }
}
