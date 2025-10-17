<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Product;

use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsProductImagesUpdated;
use Psr\Log\LoggerInterface;

final class DsProductImagesUpdatedEventHandler
{
    public function __construct(
        private readonly AeProductImportRepositoryInterface $productImportRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(DsProductImagesUpdated $event): void
    {
        $aeProductImport = $this->productImportRepository->findOneByAeProductId((int) $event->getDsProductId());

        if (null === $aeProductImport) {
            $this->logger->error('Product import data with dsProductId - '.$event->getDsProductId().' - not found');

            return;
        }

        $aeProductImport->incrementProgress();
        $this->productImportRepository->save($aeProductImport);
    }
}
