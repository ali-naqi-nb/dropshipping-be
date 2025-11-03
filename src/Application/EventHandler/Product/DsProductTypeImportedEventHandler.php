<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Product;

use App\Application\Service\ProductServiceInterface;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsProductTypeImported;
use Psr\Log\LoggerInterface;

final class DsProductTypeImportedEventHandler
{
    public function __construct(
        private readonly ProductServiceInterface $productService,
        private readonly AeProductImportRepositoryInterface $aeProductImportRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(DsProductTypeImported $event): void
    {
        $this->logger->info('[IMPORT] DsProductTypeImported received - ', [
            'productTypeId' => $event->getProductTypeId(),
            'productTypeName' => $event->getProductTypeName(),
            'dsProductId' => $event->getDsProductId(),
        ]);

        $aeProductImport = $this->aeProductImportRepository->findOneByAeProductId($event->getDsProductId());

        if (null == $aeProductImport) {
            $this->logger->error('Product import data with dsProductId - '.$event->getDsProductId().' - not found');

            return;
        }

        $attributes = $aeProductImport->getGroupData()['products'][0]['attributes'];

        $this->productService->sendDsAttributeImport(
            $event->getProductTypeId(),
            $event->getDsProductId(),
            $attributes,
        );

        $this->logger->info('[IMPORT] DsProductTypeImported sent - ', [
            'productTypeId' => $event->getProductTypeId(),
            'dsProductId' => $event->getDsProductId(),
            'attributes' => $attributes,
        ]);

        $aeProductImport->incrementProgress();
        $this->aeProductImportRepository->save($aeProductImport);
    }
}
