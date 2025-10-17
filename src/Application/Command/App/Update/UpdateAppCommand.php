<?php

declare(strict_types=1);

namespace App\Application\Command\App\Update;

use App\Application\Command\AbstractCommand;

final class UpdateAppCommand extends AbstractCommand
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $appId,
        private readonly array $config
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

    public function getConfig(): array
    {
        return $this->config;
    }
}
