<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query\App\GetAll;

use App\Application\Query\App\GetAll\GetAllAppsQuery;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class GetAllAppsQueryTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $query = new GetAllAppsQuery(TenantFactory::TENANT_ID);

        $this->assertSame(TenantFactory::TENANT_ID, $query->getTenantId());
    }
}
