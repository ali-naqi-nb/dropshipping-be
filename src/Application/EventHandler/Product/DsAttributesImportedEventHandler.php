<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Product;

use App\Application\Service\ProductServiceInterface;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Domain\Model\Product\DsAttributesImported;
use Psr\Log\LoggerInterface;

final class DsAttributesImportedEventHandler
{
    public function __construct(
        private readonly ProductServiceInterface $productService,
        private readonly AeProductImportRepositoryInterface $productImportRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(DsAttributesImported $command): void
    {
        $aeProductImport = $this->productImportRepository->findOneByAeProductId($command->getDsProductId());

        if (null == $aeProductImport) {
            $this->logger->error('Product import data with dsProductId - '.$command->getDsProductId().' - not found');

            return;
        }

        $products = $aeProductImport->getGroupData()['products'];

        for ($i = 0; $i < count($products); ++$i) {
            $products[$i]['productTypeId'] = $command->getProductTypeId();
            $products[$i]['dsVariantId'] = (string) $products[$i]['aeSkuId'];
            $products[$i]['dsProvider'] = 'Aliexpress';
            $products[$i]['attributes'] = $command->getAttributes();
        }

        $this->productService->sendDsProductGroupImport(
            $command->getDsProductId(),
            $products
        );

        $aeProductImport->incrementProgress();
        $this->productImportRepository->save($aeProductImport);
    }
}
