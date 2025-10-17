<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Domain\Model\Tenant;

use App\Infrastructure\Domain\Model\Tenant\InMemoryTenantStorage;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class InMemoryTenantStorageTest extends UnitTestCase
{
    public function testGettersAndSetters(): void
    {
        $storage = new InMemoryTenantStorage();

        $this->assertNull($storage->getId());
        $storage->setId(TenantFactory::TENANT_ID);
        $this->assertSame($storage->getId(), TenantFactory::TENANT_ID);
    }
}
