<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared\Product;

use App\Application\Shared\Product\AeProductImportResponse;
use App\Application\Shared\Product\AeProductResponse;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class AeProductImportResponseTest extends UnitTestCase
{
    public function testConstructor(): void
    {
        $items = [AeProductResponse::fromAeProduct(Factory::createAeProductImportProduct(), [])];
        $response = new AeProductImportResponse($items);

        $this->assertSame($items, $response->getItems());
    }
}
