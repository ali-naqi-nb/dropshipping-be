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
        $responses = array_map(function (AeProductImportProduct $importProduct) use ($aeDeliveryOptions) {
            return $this->responseMapper->getResponse(
                importProduct: $importProduct,
                aeDeliveryOptions: $aeDeliveryOptions[$importProduct->getAeSkuId()] ?? null
            );
        }, $importProducts);

        return new AeProductImportResponse($responses);
    }
}
