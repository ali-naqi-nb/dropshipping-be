<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Messenger;

use App\Infrastructure\Messenger\TenantIdStamp;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class TenantIdStampTest extends UnitTestCase
{
    public function testSetTenantIdCanBeSet(): void
    {
        $stamp = new TenantIdStamp(TenantFactory::TENANT_ID);

        $this->assertSame(TenantFactory::TENANT_ID, $stamp->getId());
    }
}
