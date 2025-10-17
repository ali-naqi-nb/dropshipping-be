<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\DbConfig;
use App\Tests\Shared\Factory\DbConfigFactory;
use App\Tests\Unit\UnitTestCase;

final class DbConfigTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $tenantId = DbConfigFactory::TENANT_ID;
        $user = DbConfigFactory::getUser();
        $password = DbConfigFactory::PASSWORD;
        $database = DbConfigFactory::getDatabase();
        $host = DbConfigFactory::HOST;
        $port = DbConfigFactory::PORT;

        $dbConfig = new DbConfig($tenantId, $user, $password, $database, $host, $port);

        $this->assertSame($tenantId, $dbConfig->getTenantId());
        $this->assertSame($user, $dbConfig->getUser());
        $this->assertSame($password, $dbConfig->getPassword());
        $this->assertSame($database, $dbConfig->getDatabase());
        $this->assertSame($host, $dbConfig->getDbHost());
        $this->assertSame($port, $dbConfig->getDbPort());
        $this->assertSame(DbConfigFactory::getString($dbConfig), (string) $dbConfig);
    }

    public function testFromStringCreatesCorrectObject(): void
    {
        $tenantId = DbConfigFactory::TENANT_ID;

        $dbConfig = DbConfig::fromString($tenantId, DbConfigFactory::getString());

        $this->assertSame($tenantId, $dbConfig->getTenantId());
        $this->assertSame(DbConfigFactory::getUser(), $dbConfig->getUser());
        $this->assertSame(DbConfigFactory::PASSWORD, $dbConfig->getPassword());
        $this->assertSame(DbConfigFactory::getDatabase(), $dbConfig->getDatabase());
        $this->assertSame(DbConfigFactory::HOST, $dbConfig->getDbHost());
        $this->assertSame(DbConfigFactory::PORT, $dbConfig->getDbPort());
        $this->assertSame(DbConfigFactory::getString($dbConfig), (string) $dbConfig);
    }
}
