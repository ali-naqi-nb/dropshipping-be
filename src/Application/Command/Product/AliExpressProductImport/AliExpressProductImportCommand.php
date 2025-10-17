<?php

declare(strict_types=1);

namespace App\Application\Command\Product\AliExpressProductImport;

use App\Application\Command\AbstractCommand;

final class AliExpressProductImportCommand extends AbstractCommand
{
    public function __construct(
        private readonly string $aeProductUrl,
        private readonly string $aeProductShipsTo,
    ) {
    }

    public function getAeProductUrl(): string
    {
        return $this->aeProductUrl;
    }

    public function getAeProductShipsTo(): string
    {
        return $this->aeProductShipsTo;
    }
}
