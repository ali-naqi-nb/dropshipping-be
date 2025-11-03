<?php

declare(strict_types=1);

namespace App\Application\Query\App\GetAll;

use App\Domain\Model\Bus\Query\QueryInterface;

final class GetAllAppsQuery implements QueryInterface
{
    public function __construct(private readonly string $tenantId)
    {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }
}
