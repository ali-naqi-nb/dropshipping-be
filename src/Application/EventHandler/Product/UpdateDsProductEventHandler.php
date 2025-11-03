<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Product;

use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\UpdateDsProduct;
use Psr\Log\LoggerInterface;

final class UpdateDsProductEventHandler
{
    public function __construct(
        private readonly AeProductImportProductRepositoryInterface $productImportRepository,
        private readonly LoggerInterface $logger
    )
    {
    }

    public function __invoke(UpdateDsProduct $event): void
    {
        $product = $event->getProduct();
        $productId = $product->getProductId();
        $stock = $product->getStock();
        $cost = $product->getCost();
        $currencyCode = $product->getCurrencyCode();

        $this->logger->info('Received UpdateDsProduct event', [
            'tenantId' => $event->getTenantId(),
            'dsProvider' => $event->getDsProvider(),
            'productId' => $productId,
            'stock' => $stock,
            'cost' => $cost,
            'currencyCode' => $currencyCode,
        ]);

        $aeProductImport = $this->productImportRepository->findOneByNbProductId($productId);

        if (null === $aeProductImport) {
            $this->logger->warning('Product import not found for product update', [
                'productId' => $productId,
                'tenantId' => $event->getTenantId(),
            ]);

            return;
        }

        $aeProductImport->setAeProductStock($stock);
        $aeProductImport->setAeOfferSalePrice($cost);
        $aeProductImport->setAeSkuCurrencyCode($currencyCode);

        $this->productImportRepository->save($aeProductImport);

        $this->logger->info('Successfully updated product from UpdateDsProduct event', [
            'productId' => $productId,
            'newStock' => $stock,
            'newCost' => $cost,
        ]);
    }
}
