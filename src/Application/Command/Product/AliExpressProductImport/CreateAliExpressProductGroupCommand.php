<?php

declare(strict_types=1);

namespace App\Application\Command\Product\AliExpressProductImport;

use App\Application\Command\AbstractCommand;
use App\Domain\Model\Product\AeProductImportGroupProductData;

final class CreateAliExpressProductGroupCommand extends AbstractCommand
{
//    /**
//     * @param aeProductImportGroupProductData[] $products
//     */
    public function __construct(
        private readonly array $products
    ) {
    }

    public function getProducts(): array
    {
        return $this->products;
    }
}
