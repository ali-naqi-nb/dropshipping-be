<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Rpc\V1\Product;

use App\Application\Shared\Product\DsProductAckResponse;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Product\DsProductImagesUpdated;
use App\Infrastructure\Rpc\Attribute\Rpc;

#[Rpc(command: 'dsProductImagesUpdated')]
final class DsProductImagesUpdatedAction
{
    public function __construct(private readonly EventBusInterface $bus)
    {
    }

    public function __invoke(DsProductImagesUpdated $event): DsProductAckResponse
    {
        $this->bus->publish($event);

        return DsProductAckResponse::fromEvent($event);
    }
}
