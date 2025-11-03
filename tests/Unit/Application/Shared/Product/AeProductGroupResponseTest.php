<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared\Product;

use App\Application\Shared\Product\AeProductGroupResponse;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class AeProductGroupResponseTest extends UnitTestCase
{
    public function testConstructor(): void
    {
        $id = '550e8400-e29b-41d4-a716-446655440000';
        $aeProductId = Factory::AE_PRODUCT_ID;
        $progressStep = 2;
        $totalSteps = 5;

        $response = new AeProductGroupResponse(
            id: $id,
            aeProductId: $aeProductId,
            progressStep: $progressStep,
            totalSteps: $totalSteps
        );

        $this->assertSame($id, $response->getId());
        $this->assertSame($aeProductId, $response->getAeProductId());
        $this->assertSame($progressStep, $response->getProgressStep());
        $this->assertSame($totalSteps, $response->getTotalSteps());
    }
}
