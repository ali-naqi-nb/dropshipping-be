<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Logger;

use App\Infrastructure\Domain\Model\Tenant\InMemoryTenantStorage;
use App\Infrastructure\Logger\TenantIdProcessor;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\TenantFactory;
use Monolog\Level;
use Monolog\LogRecord;

final class TenantIdProcessorTest extends IntegrationTestCase
{
    public function testProcessorWithEmptyTenantId(): void
    {
        $tenantStorage = new InMemoryTenantStorage();
        $processor = new TenantIdProcessor($tenantStorage);
        $record = new LogRecord(new \DateTimeImmutable(), 'channel', Level::Debug, 'message');

        $this->assertSame(['tenant_id' => '???'], $processor($record)->extra);
    }

    public function testProcessorWithCustomTenantId(): void
    {
        $tenantStorage = new InMemoryTenantStorage();
        $tenantStorage->setId(TenantFactory::TENANT_ID);
        $processor = new TenantIdProcessor($tenantStorage);
        $record = new LogRecord(new \DateTimeImmutable(), 'channel', Level::Debug, 'message');

        $this->assertMatchesPattern(['tenant_id' => TenantFactory::TENANT_ID], $processor($record)->extra);
    }
}
