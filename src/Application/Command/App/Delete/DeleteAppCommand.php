<?php

declare(strict_types=1);

namespace App\Application\Command\App\Delete;

use App\Application\Command\AbstractCommand;

final class DeleteAppCommand extends AbstractCommand
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $appId,
    ) {
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
