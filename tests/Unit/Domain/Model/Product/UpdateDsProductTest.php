<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Bus\Event\DomainEventInterface;
use App\Domain\Model\Product\DsProduct;
use App\Domain\Model\Product\UpdateDsProduct;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\ProductFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Unit\UnitTestCase;

final class UpdateDsProductTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $product = new DsProduct(
            productId: ProductFactory::ID,
            stock: 100,
            cost: 5000,
            currencyCode: TenantFactory::CURRENCY_EUR
        );

        $event = new UpdateDsProduct(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            product: $product
        );

        $this->assertSame(TenantFactory::TENANT_ID, $event->getTenantId());
        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $event->getDsProvider());
        $this->assertSame($product, $event->getProduct());
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $product = new DsProduct(
            productId: ProductFactory::ID,
            stock: 100,
            cost: 5000,
            currencyCode: TenantFactory::CURRENCY_EUR
        );

        $event = new UpdateDsProduct(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            product: $product
        );

        $array = $event->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('tenantId', $array);
        $this->assertArrayHasKey('dsProvider', $array);
        $this->assertArrayHasKey('product', $array);

        $this->assertSame(TenantFactory::TENANT_ID, $array['tenantId']);
        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $array['dsProvider']);

        $this->assertIsArray($array['product']);
        $this->assertArrayHasKey('productId', $array['product']);
        $this->assertArrayHasKey('stock', $array['product']);
        $this->assertArrayHasKey('cost', $array['product']);
        $this->assertArrayHasKey('currencyCode', $array['product']);

        $this->assertSame(ProductFactory::ID, $array['product']['productId']);
        $this->assertSame(100, $array['product']['stock']);
        $this->assertSame(5000, $array['product']['cost']);
        $this->assertSame(TenantFactory::CURRENCY_EUR, $array['product']['currencyCode']);
    }

    public function testEventImplementsDomainEventInterface(): void
    {
        $product = new DsProduct(
            productId: ProductFactory::ID,
            stock: 50,
            cost: 2500,
            currencyCode: TenantFactory::DEFAULT_CURRENCY
        );

        $event = new UpdateDsProduct(
            tenantId: TenantFactory::TENANT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            product: $product
        );

        $this->assertInstanceOf(DomainEventInterface::class, $event);
    }

    public function testCreateEventWithDifferentValues(): void
    {
        $product = new DsProduct(
            productId: 'another-product-id',
            stock: 0,
            cost: 0,
            currencyCode: 'USD'
        );

        $event = new UpdateDsProduct(
            tenantId: TenantFactory::SECOND_TENANT_ID,
            dsProvider: DsProviderFactory::ALI_EXPRESS,
            product: $product
        );

        $this->assertSame(TenantFactory::SECOND_TENANT_ID, $event->getTenantId());
        $this->assertSame(DsProviderFactory::ALI_EXPRESS, $event->getDsProvider());
        $this->assertSame($product, $event->getProduct());
        $this->assertSame('another-product-id', $event->getProduct()->getProductId());
        $this->assertSame(0, $event->getProduct()->getStock());
        $this->assertSame(0, $event->getProduct()->getCost());
        $this->assertSame('USD', $event->getProduct()->getCurrencyCode());
    }
}
