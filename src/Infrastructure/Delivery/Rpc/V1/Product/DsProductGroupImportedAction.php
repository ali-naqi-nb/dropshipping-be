<?php

namespace App\Infrastructure\Delivery\Rpc\V1\Product;

use App\Application\Shared\Product\DsProductAckResponse;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Product\DsProductGroupImported;
use App\Infrastructure\Rpc\Attribute\Rpc;

#[Rpc(command: 'dsProductGroupImported')]
final class DsProductGroupImportedAction
{
    public function __construct(private readonly EventBusInterface $bus)
    {
    }

    public function __invoke(DsProductGroupImported $event): DsProductAckResponse
    {
        $this->bus->publish($event);

        return DsProductAckResponse::fromEvent($event);
    }
}
