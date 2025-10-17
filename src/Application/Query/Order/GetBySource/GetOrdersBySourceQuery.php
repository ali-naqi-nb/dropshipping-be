<?php

declare(strict_types=1);

namespace App\Application\Query\Order\GetBySource;

use App\Domain\Model\Bus\Query\QueryInterface;

final class GetOrdersBySourceQuery implements QueryInterface
{
    public function __construct(private readonly string $tenantId, private readonly string $source)
    {
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
