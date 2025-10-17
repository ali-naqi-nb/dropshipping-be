<?php

declare(strict_types=1);

namespace App\Application\Command\App\Authorize;

use App\Application\Command\AbstractCommand;

final class AuthorizeAppCommand extends AbstractCommand
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $appId,
        private readonly string $token,
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

    public function getToken(): string
    {
        return $this->token;
    }
}
