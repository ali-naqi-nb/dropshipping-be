<?php

namespace App\Infrastructure\Delivery\Rpc\V1\Product;

use App\Application\Shared\Product\DsProductAckResponse;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Product\DsProductTypeImported;
use App\Infrastructure\Rpc\Attribute\Rpc;

#[Rpc(command: 'dsProductTypeImported')]
final class DsProductTypeImportedAction
{
    public function __construct(private readonly EventBusInterface $bus)
    {
    }

    public function __invoke(DsProductTypeImported $event): DsProductAckResponse
    {
        $this->bus->publish($event);

        return DsProductAckResponse::fromEvent($event);
    }
}
