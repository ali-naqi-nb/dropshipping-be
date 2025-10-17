<?php

declare(strict_types=1);

namespace App\Domain\Model\Order;

use App\Domain\Model\Bus\Event\DomainEventInterface;

final class DsOrderStatusUpdated implements DomainEventInterface
{
    public function __construct(
        private readonly string $nbOrderId,
        private readonly ProcessingStatus $nbOrderStatus,
    ) {
    }

    public function getNbOrderId(): string
    {
        return $this->nbOrderId;
    }

    public function getNbOrderStatus(): ProcessingStatus
    {
        return $this->nbOrderStatus;
    }
}
