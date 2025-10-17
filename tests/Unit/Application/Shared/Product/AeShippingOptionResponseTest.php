<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shared\Product;

use App\Application\Service\AliExpress\AeUtil;
use App\Application\Shared\Product\AeShippingOptionResponse;
use App\Tests\Shared\Factory\AeProductImportProductFactory as Factory;
use App\Tests\Unit\UnitTestCase;

final class AeShippingOptionResponseTest extends UnitTestCase
{
    public function testFromAeDeliveryOption(): void
    {
        $aeDeliveryOption = Factory::createAeDeliveryOption();
        $response = AeShippingOptionResponse::fromAeDeliveryOption($aeDeliveryOption);

        $this->assertSame($aeDeliveryOption['code'], $response->getCode());
        $this->assertSame($aeDeliveryOption['ship_from_country'], $response->getShipsFrom());
        $this->assertSame($aeDeliveryOption['max_delivery_days'], $response->getMaxDeliveryDays());
        $this->assertSame($aeDeliveryOption['min_delivery_days'], $response->getMinDeliveryDays());
        $this->assertSame(AeUtil::toBase100($aeDeliveryOption['shipping_fee_cent']), $response->getShippingFeePrice());
        $this->assertSame($aeDeliveryOption['shipping_fee_currency'], $response->getShippingFeeCurrency());
    }
}
