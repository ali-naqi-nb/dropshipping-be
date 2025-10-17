<?php

declare(strict_types=1);

namespace App\Domain\Model\Tenant;

interface TenantStorageInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;
}
