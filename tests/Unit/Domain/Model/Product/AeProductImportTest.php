<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Product;

use App\Domain\Model\Product\AeProductImport;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Unit\UnitTestCase;

final class AeProductImportTest extends UnitTestCase
{
    public function testGettersAndSetters(): void
    {
        $aeProductImport = new AeProductImport(
            groupData: AeProductImportFactory::GROUP_DATA,
            aeProductId: AeProductImportFactory::AE_PRODUCT_ID,
            completedStep: 3
        );

        $this->assertIsString($aeProductImport->getId());
        $this->assertSame(AeProductImportFactory::GROUP_DATA, $aeProductImport->getGroupData());
        $this->assertSame(AeProductImportFactory::AE_PRODUCT_ID, $aeProductImport->getAeProductId());
        $this->assertSame(3, $aeProductImport->getCompletedStep());
        $this->assertSame(5, $aeProductImport->getTotalSteps());

        $aeProductImport->incrementProgress();
        $this->assertSame(4, $aeProductImport->getCompletedStep());

        $aeProductImport->setAeProductId(AeProductImportProductFactory::NEW_AE_PRODUCT_ID);
        $this->assertSame(AeProductImportProductFactory::NEW_AE_PRODUCT_ID, $aeProductImport->getAeProductId());

        $aeProductImport->setGroupData([]);
        $this->assertSame([], $aeProductImport->getGroupData());

        $aeProductImport->setShippingOptions([]);
        $this->assertSame([], $aeProductImport->getShippingOptions());

        $aeProductImport->setTotalSteps(6);
        $this->assertSame(6, $aeProductImport->getTotalSteps());
    }
}
