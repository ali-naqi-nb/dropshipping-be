<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\Product\SyncAliExpressProducts;

use App\Application\Command\Product\SyncAliExpressProducts\SyncAliExpressProductsResult;
use App\Tests\Unit\UnitTestCase;

final class SyncAliExpressProductsResultTest extends UnitTestCase
{
    public function testInitialState(): void
    {
        $result = new SyncAliExpressProductsResult();

        // Tenant metrics
        $this->assertSame(0, $result->getSuccessfulTenants());
        $this->assertSame(0, $result->getFailedTenants());
        $this->assertSame(0, $result->getSkippedTenants());
        $this->assertSame(0, $result->getTotalTenantsProcessed());

        // Product metrics
        $this->assertSame(0, $result->getSuccessfulProducts());
        $this->assertSame(0, $result->getFailedProducts());
        $this->assertSame(0, $result->getTotalProductsProcessed());

        // Variant metrics
        $this->assertSame(0, $result->getVariantsUpdated());
        $this->assertSame(0, $result->getVariantsSkipped());
        $this->assertSame(0, $result->getVariantsWithErrors());
    }

    public function testIncrementTenantMethods(): void
    {
        $result = new SyncAliExpressProductsResult();

        $result->incrementSuccessfulTenants();
        $result->incrementSuccessfulTenants();
        $result->incrementFailedTenants();
        $result->incrementSkippedTenants();

        $this->assertSame(2, $result->getSuccessfulTenants());
        $this->assertSame(1, $result->getFailedTenants());
        $this->assertSame(1, $result->getSkippedTenants());
        $this->assertSame(4, $result->getTotalTenantsProcessed());
    }

    public function testIncrementProductMethods(): void
    {
        $result = new SyncAliExpressProductsResult();

        $result->incrementSuccessfulProducts();
        $result->incrementSuccessfulProducts();
        $result->incrementFailedProducts();

        $this->assertSame(2, $result->getSuccessfulProducts());
        $this->assertSame(1, $result->getFailedProducts());
        $this->assertSame(3, $result->getTotalProductsProcessed());
    }

    public function testIncrementVariantMethods(): void
    {
        $result = new SyncAliExpressProductsResult();

        $result->incrementVariantsUpdated();
        $result->incrementVariantsUpdated();
        $result->incrementVariantsUpdated();
        $result->incrementVariantsSkipped();
        $result->incrementVariantsWithErrors();

        $this->assertSame(3, $result->getVariantsUpdated());
        $this->assertSame(1, $result->getVariantsSkipped());
        $this->assertSame(1, $result->getVariantsWithErrors());
    }

    public function testToArray(): void
    {
        $result = new SyncAliExpressProductsResult();
        $result->incrementSuccessfulTenants();
        $result->incrementFailedTenants();
        $result->incrementSuccessfulProducts();
        $result->incrementVariantsUpdated();

        $array = $result->toArray();

        $this->assertIsArray($array);

        // Tenant keys
        $this->assertArrayHasKey('totalTenantsProcessed', $array);
        $this->assertArrayHasKey('successfulTenants', $array);
        $this->assertArrayHasKey('failedTenants', $array);
        $this->assertArrayHasKey('skippedTenants', $array);

        // Product keys
        $this->assertArrayHasKey('totalProductsProcessed', $array);
        $this->assertArrayHasKey('successfulProducts', $array);
        $this->assertArrayHasKey('failedProducts', $array);

        // Variant keys
        $this->assertArrayHasKey('variantsUpdated', $array);
        $this->assertArrayHasKey('variantsSkipped', $array);
        $this->assertArrayHasKey('variantsWithErrors', $array);

        // Verify values
        $this->assertSame(2, $array['totalTenantsProcessed']);
        $this->assertSame(1, $array['successfulTenants']);
        $this->assertSame(1, $array['failedTenants']);
        $this->assertSame(1, $array['successfulProducts']);
        $this->assertSame(1, $array['variantsUpdated']);
    }
}
