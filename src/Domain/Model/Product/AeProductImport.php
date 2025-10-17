<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

use Symfony\Component\Uid\Uuid;

class AeProductImport
{
    public const DEFAULT_STEPS = 5;
    private Uuid $id;
    private int|string|null $aeProductId;
    private int $completedStep;
    private int $totalSteps;
    private ?array $shippingOptions;
    private array $groupData;

    public function __construct(
        array $groupData,
        ?string $id = null,
        int|string|null $aeProductId = null,
        int $completedStep = 0,
        int $totalSteps = self::DEFAULT_STEPS,
        ?array $shippingOptions = null
    ) {
        $this->id = (null !== $id) ? Uuid::fromString($id) : Uuid::v4();
        $this->aeProductId = $aeProductId;
        $this->completedStep = $completedStep;
        $this->totalSteps = $totalSteps;
        $this->shippingOptions = $shippingOptions;
        $this->groupData = $groupData;
    }

    public function getId(): string
    {
        return $this->id->__toString();
    }

    public function getAeProductId(): int|string|null
    {
        return $this->aeProductId;
    }

    public function setAeProductId(int|string|null $aeProductId): void
    {
        $this->aeProductId = $aeProductId;
    }

    public function getCompletedStep(): int
    {
        return $this->completedStep;
    }

    public function getTotalSteps(): int
    {
        return $this->totalSteps;
    }

    public function setTotalSteps(int $totalSteps): void
    {
        $this->totalSteps = $totalSteps;
    }

    public function incrementProgress(): void
    {
        ++$this->completedStep;
    }

    public function getShippingOptions(): ?array
    {
        return $this->shippingOptions;
    }

    public function setShippingOptions(?array $shippingOptions): void
    {
        $this->shippingOptions = $shippingOptions;
    }

    public function getGroupData(): array
    {
        return $this->groupData;
    }

    public function setGroupData(array $groupData): void
    {
        $this->groupData = $groupData;
    }
}
