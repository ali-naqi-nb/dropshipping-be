<?php

declare(strict_types=1);

namespace App\Domain\Model\Order;

use DateTimeInterface;
use Symfony\Component\Uid\Uuid;

class DsOrderMapping
{
    private Uuid $id;
    private Uuid $nbOrderId;
    private string $dsOrderId;
    private string $dsProvider;
    private ?string $dsStatus;
    private ?DateTimeInterface $createdAt = null;
    private ?DateTimeInterface $updatedAt = null;

    public function __construct(
        string $id,
        string $nbOrderId,
        string $dsOrderId,
        string $dsProvider,
        ?string $dsStatus = null
    ) {
        $this->setId($id);
        $this->setNbOrderId($nbOrderId);
        $this->dsOrderId = $dsOrderId;
        $this->dsProvider = $dsProvider;
        $this->dsStatus = $dsStatus;
    }

    private function setId(string $id): void
    {
        $this->id = Uuid::fromString($id);
    }

    public function getId(): string
    {
        return (string) $this->id;
    }

    private function setNbOrderId(string $nbOrderId): void
    {
        $this->nbOrderId = Uuid::fromString($nbOrderId);
    }

    public function getNbOrderId(): string
    {
        return (string) $this->nbOrderId;
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

    public function setDsStatus(string $dsStatus): void
    {
        $this->dsStatus = $dsStatus;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
