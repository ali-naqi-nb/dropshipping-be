<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use App\Domain\Model\Tenant\TenantStorageInterface;
use Monolog\LogRecord;

final class TenantIdProcessor
{
    public function __construct(private readonly TenantStorageInterface $tenantStorage)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['tenant_id'] = $this->tenantStorage->getId() ?: '???';

        return $record;
    }
}
