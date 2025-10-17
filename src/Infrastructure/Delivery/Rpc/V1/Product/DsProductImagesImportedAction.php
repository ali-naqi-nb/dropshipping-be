<?php

namespace App\Infrastructure\Delivery\Rpc\V1\Product;

use App\Application\Shared\Product\DsProductAckResponse;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Product\DsProductImagesImported;
use App\Infrastructure\Rpc\Attribute\Rpc;

#[Rpc(command: 'dsProductImagesImported')]
final class DsProductImagesImportedAction
{
    public function __construct(private readonly EventBusInterface $bus)
    {
    }

    public function __invoke(DsProductImagesImported $event): DsProductAckResponse
    {
        $this->bus->publish($event);

        return DsProductAckResponse::fromEvent($event);
    }
}
