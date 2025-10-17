<?php

declare(strict_types=1);

namespace App\Application\Service\Product;

use App\Application\Shared\Product\AeProductResponse;
use App\Application\Shared\Product\AeShippingOptionResponse;
use App\Domain\Model\Product\AeProductImportProduct;

final class AeProductResponseMapper
{
    /**
     * @param ?array<array<string, mixed>> $aeDeliveryOptions
     */
    public function getResponse(
        AeProductImportProduct $importProduct,
        ?array $aeDeliveryOptions,
    ): AeProductResponse {
        $aeDeliveryOptions = $aeDeliveryOptions ?? [];

        $shippingOptionResponses = array_map(
            fn (array $aeDeliveryOption) => AeShippingOptionResponse::fromAeDeliveryOption($aeDeliveryOption),
            $aeDeliveryOptions
        );

        return AeProductResponse::fromAeProduct($importProduct, $shippingOptionResponses);
    }
}
