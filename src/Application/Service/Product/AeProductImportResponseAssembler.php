<?php

declare(strict_types=1);

namespace App\Application\Service\Product;

use App\Application\Shared\Product\AeProductImportResponse;
use App\Domain\Model\Product\AeProductImportProduct;

final class AeProductImportResponseAssembler
{
    public function __construct(private readonly AeProductResponseMapper $responseMapper)
    {
    }

    /**
     * @param AeProductImportProduct[]                 $importProducts
     * @param array<int, ?array<array<string, mixed>>> $aeDeliveryOptions
     */
    public function assembleAeProductResponse(array $importProducts, array $aeDeliveryOptions): AeProductImportResponse
    {
        $responses = [];

        foreach ($importProducts as $importProduct) {
            $deliveryOptions = $aeDeliveryOptions[$importProduct->getAeSkuId()] ?? null;

            // Skip products that don't have delivery options
            if (null === $deliveryOptions || empty($deliveryOptions)) {
                continue;
            }

            $responses[] = $this->responseMapper->getResponse(
                importProduct: $importProduct,
                aeDeliveryOptions: $deliveryOptions
            );
        }

        return new AeProductImportResponse($responses);
    }
}
