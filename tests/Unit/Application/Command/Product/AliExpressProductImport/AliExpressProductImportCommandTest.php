<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\Product\AliExpressProductImport;

use App\Application\Command\Product\AliExpressProductImport\AliExpressProductImportCommand;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class AliExpressProductImportCommandTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $command = new AliExpressProductImportCommand(
            Factory::AE_PRODUCT_URL,
            Factory::AE_PRODUCT_SHIPS_TO,
        );

        $this->assertSame(Factory::AE_PRODUCT_URL, $command->getAeProductUrl());
        $this->assertSame(Factory::AE_PRODUCT_SHIPS_TO, $command->getAeProductShipsTo());
    }
}
