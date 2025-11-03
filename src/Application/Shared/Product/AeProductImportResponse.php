<?php

declare(strict_types=1);

namespace App\Application\Shared\Product;

use App\Domain\Model\Bus\Command\CommandResponseInterface;
use App\Domain\Model\Bus\Query\QueryResponseInterface;

final class AeProductImportResponse implements QueryResponseInterface, CommandResponseInterface
{
    /**
     * @param AeProductResponse[] $items
     */
    public function __construct(private readonly array $items)
    {
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
