<?php

declare(strict_types=1);

namespace App\Application\EventHandler\Tenant;

use App\Domain\Model\Tenant\DropshippingDbCreated;
use App\Domain\Model\Tenant\TenantServiceInterface;

final class DbCreatedHandler
{
    public function __construct(private readonly TenantServiceInterface $tenantService)
    {
    }

    public function __invoke(DropshippingDbCreated $event): void
    {
        $this->tenantService->create($event);
    }
}
