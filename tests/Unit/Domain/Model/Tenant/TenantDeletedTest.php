<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\TenantDeleted;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class TenantDeletedTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $event = new TenantDeleted(TenantFactory::TENANT_ID);

        $this->assertSame(TenantFactory::TENANT_ID, $event->getTenantId());
    }
}
