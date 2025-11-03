<?php

declare(strict_types=1);

namespace App\Application\Shared\Product;

use App\Domain\Model\Product\DsAttributesImported;
use App\Domain\Model\Product\DsProductGroupImported;
use App\Domain\Model\Product\DsProductImagesImported;
use App\Domain\Model\Product\DsProductImagesUpdated;
use App\Domain\Model\Product\DsProductTypeImported;

final class DsProductAckResponse
{
    public const STATUS_ACK = 'ACK';

    private function __construct(
        private readonly int|string $dsProductId,
        private readonly string $dsProvider,
        private string $status = self::STATUS_ACK,
    ) {
    }

    public function getDsProductId(): int|string
    {
        return $this->dsProductId;
    }

    public function getDsProvider(): string
    {
        return $this->dsProvider;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public static function fromEvent(DsAttributesImported|DsProductTypeImported
                                             |DsProductGroupImported|DsProductImagesImported
                                             |DsProductImagesUpdated $data
    ): self {
        return new self(
            dsProductId: $data->getDsProductId(),
            dsProvider: $data->getDsProvider(),
            status: self::STATUS_ACK,
        );
    }
}
