<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Domain\Model\Order\DsOrderConfirmed;
use App\Domain\Model\Order\DsProvider;
use App\Tests\Shared\Factory\OrderFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class DsOrderConfirmedTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $event = new DsOrderConfirmed(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProvider::AliExpress->value,
            orderId: OrderFactory::NON_EXISTING_ORDER_ID,
        );

        $this->assertInstanceOf(DsOrderConfirmed::class, $event);
        $this->assertSame(TenantFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame(DsProvider::AliExpress->value, $event->getDsProvider());
        $this->assertSame(OrderFactory::NON_EXISTING_ORDER_ID, $event->getOrderId());
    }
}
