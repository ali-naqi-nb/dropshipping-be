<?php

declare(strict_types=1);

namespace App\Application\Shared\Product;

use App\Domain\Model\Bus\Command\CommandResponseInterface;

final class AeProductGroupResponse implements CommandResponseInterface
{
    public function __construct(
        private readonly string $id,
        private readonly int|string $aeProductId,
        private readonly int $progressStep,
        private readonly int $totalSteps,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAeProductId(): int|string
    {
        return $this->aeProductId;
    }

    public function getProgressStep(): int
    {
        return $this->progressStep;
    }

    public function getTotalSteps(): int
    {
        return $this->totalSteps;
    }
}
