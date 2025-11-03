<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\Product\SyncAliExpressProducts;

use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsCommand;
use App\Tests\Unit\UnitTestCase;

final class SyncAliExpressProductsCommandTest extends UnitTestCase
{
    public function testGettersWithDefaults(): void
    {
        $command = new SyncAliExpressProductsCommand();

        $this->assertFalse($command->isDryRun());
        $this->assertNull($command->getTenantId());
        $this->assertSame(SyncAliExpressProductsCommand::DEFAULT_TIMEOUT_MINUTES, $command->getTimeoutMinutes());
        $this->assertSame(SyncAliExpressProductsCommand::DEFAULT_TIMEOUT_MINUTES * 60, $command->getTimeoutSeconds());
    }

    public function testGettersWithCustomValues(): void
    {
        $command = new SyncAliExpressProductsCommand(
            dryRun: true,
            tenantId: 'test-tenant-123',
            timeoutMinutes: 60,
        );

        $this->assertTrue($command->isDryRun());
        $this->assertSame('test-tenant-123', $command->getTenantId());
        $this->assertSame(60, $command->getTimeoutMinutes());
        $this->assertSame(3600, $command->getTimeoutSeconds());
    }

    public function testGettersWithTenantId(): void
    {
        $command = new SyncAliExpressProductsCommand(
            tenantId: 'abc-def-456',
        );

        $this->assertFalse($command->isDryRun());
        $this->assertSame('abc-def-456', $command->getTenantId());
        $this->assertSame(SyncAliExpressProductsCommand::DEFAULT_TIMEOUT_MINUTES, $command->getTimeoutMinutes());
    }

    public function testGettersWithTimeout(): void
    {
        $command = new SyncAliExpressProductsCommand(
            timeoutMinutes: 45,
        );

        $this->assertFalse($command->isDryRun());
        $this->assertNull($command->getTenantId());
        $this->assertSame(45, $command->getTimeoutMinutes());
        $this->assertSame(2700, $command->getTimeoutSeconds());
    }
}
