<?php

namespace App\Application\Service\App;

use App\Domain\Model\Tenant\App;
use Exception;

interface AppAccessTokenManagerInterface
{
    /** @throws Exception */
    public function exchangeTemporaryTokenWithAccessToken(string $tenantId, string $token): ?App;

    public function isAccessTokenExpired(string $tenantId): bool;

    /** @throws Exception */
    public function refreshAccessToken(string $tenantId): ?App;

    public function getAccessToken(string $tenantId): string;
}
