<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query\Order\GetBySource;

use App\Application\Query\Order\GetBySource\GetOrdersBySourceResponse;
use App\Tests\Shared\Factory\DsOrderMappingFactory;
use App\Tests\Unit\UnitTestCase;

final class GetOrdersBySourceResponseTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $query = GetOrdersBySourceResponse::fromDsOrder(
            DsOrderMappingFactory::createDsOrderMapping(
                id: DsOrderMappingFactory::FIRST_ORDER_ID,
                nbOrderId: DsOrderMappingFactory::FIRST_ORDER_NB_ORDER_ID,
                dsOrderId: DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID,
                dsProvider: DsOrderMappingFactory::FIRST_ORDER_DS_PROVIDER,
                dsStatus: DsOrderMappingFactory::FIRST_ORDER_DS_STATUS
            )
        );

        $this->assertSame(DsOrderMappingFactory::FIRST_ORDER_ID, $query->getId());
        $this->assertSame(DsOrderMappingFactory::FIRST_ORDER_NB_ORDER_ID, $query->getNbOrderId());
        $this->assertSame(DsOrderMappingFactory::FIRST_ORDER_DS_ORDER_ID, $query->getDsOrderId());
        $this->assertSame(DsOrderMappingFactory::FIRST_ORDER_DS_PROVIDER, $query->getDsProvider());
        $this->assertSame(DsOrderMappingFactory::FIRST_ORDER_DS_STATUS, $query->getDsStatus());
        $this->assertNull($query->getCreatedAt());
        $this->assertNull($query->getUpdatedAt());
    }
}
