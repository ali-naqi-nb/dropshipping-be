<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Tenant;

use App\Domain\Model\Tenant\TenantStorageInterface;

final class InMemoryTenantStorage implements TenantStorageInterface
{
    private ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
