<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query\App\Get;

use App\Application\Query\App\Get\GetAppQuery;
use App\Tests\Shared\Factory\AppFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class GetAppQueryTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $query = new GetAppQuery(TenantFactory::TENANT_ID, AppFactory::ALI_EXPRESS_ID);

        $this->assertSame(TenantFactory::TENANT_ID, $query->getTenantId());
        $this->assertSame(AppFactory::ALI_EXPRESS_ID, $query->getAppId());
    }
}
