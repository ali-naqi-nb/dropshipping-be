<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Product;

use App\Application\Service\FileServiceInterface;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsProductGroupImported;
use Psr\Log\LoggerInterface;

final class DsProductGroupImportedEventHandler
{
    public function __construct(
        private readonly AeProductImportRepositoryInterface $productImportRepository,
        private readonly AeProductImportProductRepositoryInterface $productImportProductRepository,
        private readonly FileServiceInterface $fileService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(DsProductGroupImported $event): void
    {
        $aeProductImport = $this->productImportRepository->findOneByAeProductId((int) $event->getDsProductId());

        if (null === $aeProductImport) {
            $this->logger->error('Product import data with dsProductId - '.$event->getDsProductId().' - not found');

            return;
        }

        $products = $event->getProducts();
        $groupData = $aeProductImport->getGroupData();
        $productsForImport = [];

        foreach ($products as $product) {
            $productImportProduct = $this->productImportProductRepository->findOneByAeProductIdAndAeSkuId($event->getDsProductId(), $product['dsVariantId']);

            if (null === $productImportProduct) {
                continue;
            }

            $productImportProduct->setNbProductId($product['productId']);
            $this->productImportProductRepository->save($productImportProduct);
            $productsForImport[] = [
                'dsVariantId' => $product['dsVariantId'],
                'images' => $this->getProductImages($groupData['products'], $product['dsVariantId']),
            ];
        }

        $this->fileService->sendDsProductImagesImport(
            $event->getDsProductId(),
            $productsForImport,
        );

        $aeProductImport->incrementProgress();
        $this->productImportRepository->save($aeProductImport);
    }

    private function getProductImages(array $productsInGroup, string $aeSkuId): array
    {
        $this->logger->info('[IMPORT] getProductImages - ', [
            'productsInGroup' => $productsInGroup,
        ]);
        foreach ($productsInGroup as $product) {
            $this->logger->info('[IMPORT] getProductImages checking - ', [
                '$product[\'aeSkuId\']' => $product['aeSkuId'],
                '$aeSkuId' => $aeSkuId,
            ]);
            if ((string) $product['aeSkuId'] === (string) $aeSkuId) {
                return $product['images'];
            }
        }

        return [];
    }
}
