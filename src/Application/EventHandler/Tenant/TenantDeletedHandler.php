<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Tenant;

use App\Domain\Model\Tenant\TenantDeleted;
use App\Domain\Model\Tenant\TenantServiceInterface;

final class TenantDeletedHandler
{
    public function __construct(private readonly TenantServiceInterface $tenantService)
    {
    }

    public function __invoke(TenantDeleted $event): void
    {
        $this->tenantService->removeTenant($event);
    }
}
