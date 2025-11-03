<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Product;

use App\Application\Service\ProductServiceInterface;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsProductImagesImported;
use Psr\Log\LoggerInterface;

final class DsProductImagesImportedEventHandler
{
    public function __construct(
        private readonly AeProductImportProductRepositoryInterface $productImportProductRepository,
        private readonly AeProductImportRepositoryInterface $productImportRepository,
        private readonly ProductServiceInterface $productService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(DsProductImagesImported $event): void
    {
        $this->logger->info('[IMPORT] DsProductImagesImported received - ', [
            'dsProductId' => $event->getDsProductId(),
            'products' => $event->getProducts(),
        ]);

        $aeProductImport = $this->productImportRepository->findOneByAeProductId((int) $event->getDsProductId());

        if (null === $aeProductImport) {
            $this->logger->error('Product import data with dsProductId - '.$event->getDsProductId().' - not found');

            return;
        }

        $updatedProducts = [];
        foreach ($event->getProducts() as $product) {
            $aeProductImportProduct = $this->productImportProductRepository->findOneByAeProductIdAndAeSkuId($event->getDsProductId(), $product['dsVariantId']);

            if (null === $aeProductImportProduct) {
                continue;
            }

            $updatedProducts[] = [
                'productId' => $aeProductImportProduct->getNbProductId(),
                'images' => $product['images'],
            ];
        }

        $this->productService->sendDsProductImagesUpdate(
            dsProductId: $event->getDsProductId(),
            products: $updatedProducts
        );

        $this->logger->info('[IMPORT] DsProductImagesImported sent - ', [
            'dsProductId' => $event->getDsProductId(),
            'products' => $updatedProducts,
        ]);

        $aeProductImport->incrementProgress();
        $this->productImportRepository->save($aeProductImport);
    }
}
