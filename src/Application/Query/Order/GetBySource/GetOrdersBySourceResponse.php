<?php

declare(strict_types=1);

namespace App\Application\Query\Order\GetBySource;

use App\Domain\Model\Bus\Query\QueryResponseInterface;
use App\Domain\Model\Order\DsOrderMapping;
use DateTimeInterface;

final class GetOrdersBySourceResponse implements QueryResponseInterface
{
    private function __construct(
        private string $id,
        private string $nbOrderId,
        private string $dsOrderId,
        private string $dsProvider,
        private ?string $dsStatus,
        private ?DateTimeInterface $createdAt,
        private ?DateTimeInterface $updatedAt
    ) {
    }

    public static function fromDsOrder(DsOrderMapping $dsOrder): self
    {
        return new self(
            (string) $dsOrder->getId(),
            (string) $dsOrder->getNbOrderId(),
            $dsOrder->getDsOrderId(),
            $dsOrder->getDsProvider(),
            $dsOrder->getDsStatus(),
            $dsOrder->getCreatedAt(),
            $dsOrder->getUpdatedAt()
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNbOrderId(): string
    {
        return $this->nbOrderId;
    }

    public function getDsOrderId(): string
    {
        return $this->dsOrderId;
    }

    public function getDsProvider(): string
    {
        return $this->dsProvider;
    }

    public function getDsStatus(): ?string
    {
        return $this->dsStatus;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}
