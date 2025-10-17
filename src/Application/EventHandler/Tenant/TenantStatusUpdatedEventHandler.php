<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Tenant;

use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Domain\Model\Tenant\TenantStatusUpdated;

final class TenantStatusUpdatedEventHandler
{
    public function __construct(private readonly TenantServiceInterface $tenantService)
    {
    }

    public function __invoke(TenantStatusUpdated $event): void
    {
        $this->tenantService->updateStatus($event);
    }
}
