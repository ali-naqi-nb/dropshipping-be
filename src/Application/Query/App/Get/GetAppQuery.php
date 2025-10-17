<?php

declare(strict_types=1);

namespace App\Application\Query\App\Get;

use App\Domain\Model\Bus\Query\QueryInterface;

final class GetAppQuery implements QueryInterface
{
    public function __construct(private readonly string $tenantId, private readonly string $appId)
    {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }
}
