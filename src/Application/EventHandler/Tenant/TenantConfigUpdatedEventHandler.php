<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Tenant;

use App\Domain\Model\Tenant\TenantConfigUpdated;
use App\Domain\Model\Tenant\TenantServiceInterface;

final class TenantConfigUpdatedEventHandler
{
    public function __construct(private readonly TenantServiceInterface $tenantService)
    {
    }

    public function __invoke(TenantConfigUpdated $event): void
    {
        $this->tenantService->update($event);
    }
}
