<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Domain\Model\Order\DsOrderCreated;
use App\Domain\Model\Order\DsOrderCreatedData;
use App\Domain\Model\Order\DsProvider;
use App\Tests\Shared\Factory\OrderFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class DsOrderCreatedTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $event = new DsOrderCreated(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProvider::AliExpress->value,
            order: new DsOrderCreatedData(
                OrderFactory::NON_EXISTING_ORDER_ID,
                OrderFactory::DS_ORDER_SHIPPING_ADDRESS,
                OrderFactory::DS_ORDER_PRODUCTS
            ),
        );
        $this->assertSame(TenantFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame(DsProvider::AliExpress->value, $event->getDsProvider());
        $this->assertSame(OrderFactory::NON_EXISTING_ORDER_ID, $event->getOrder()->getOrderId());
        $this->assertSame(OrderFactory::DS_ORDER_SHIPPING_ADDRESS, $event->getOrder()->getShippingAddress());
        $this->assertSame(OrderFactory::DS_ORDER_PRODUCTS, $event->getOrder()->getOrderProducts());
    }
}
