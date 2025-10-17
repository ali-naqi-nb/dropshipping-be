<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query\Order\GetBySource;

use App\Application\Query\Order\GetBySource\GetOrdersBySourceQuery;
use App\Domain\Model\Order\DsProvider;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class GetOrdersBySourceQueryTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $query = new GetOrdersBySourceQuery(TenantFactory::TENANT_ID, DsProvider::AliExpress->value);

        $this->assertSame(TenantFactory::TENANT_ID, $query->getTenantId());
        $this->assertSame(DsProvider::AliExpress->value, $query->getSource());
    }
}
