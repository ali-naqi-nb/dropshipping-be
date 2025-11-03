<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Order\DsProvider;
use App\Domain\Model\Product\DsProduct;
use App\Domain\Model\Product\DsProductUpdated;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class DsProductUpdatedTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $product = new DsProduct(
            'test-id',
            1,
            200,
            'USD'
        );

        $event = new DsProductUpdated(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProvider::AliExpress->value,
            product: $product
        );

        $this->assertSame(TenantFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame(DsProvider::AliExpress->value, $event->getDsProvider());
        $this->assertSame($product, $event->getProduct());
    }
}
