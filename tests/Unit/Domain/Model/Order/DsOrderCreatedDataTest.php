<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Domain\Model\Order\DsOrderCreatedData;
use App\Tests\Shared\Factory\OrderFactory;
use App\Tests\Unit\UnitTestCase;

final class DsOrderCreatedDataTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $orderProduct = new DsOrderCreatedData(
            OrderFactory::NON_EXISTING_ORDER_ID,
            OrderFactory::DS_ORDER_SHIPPING_ADDRESS,
            OrderFactory::DS_ORDER_PRODUCTS
        );

        $this->assertSame(OrderFactory::NON_EXISTING_ORDER_ID, $orderProduct->getOrderId());
        $this->assertMatchesPattern(OrderFactory::DS_ORDER_SHIPPING_ADDRESS, $orderProduct->getShippingAddress());
        $this->assertSame(OrderFactory::DS_ORDER_PRODUCTS, $orderProduct->getOrderProducts());
    }
}
