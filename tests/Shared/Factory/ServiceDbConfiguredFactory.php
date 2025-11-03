<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Tenant\ServiceDbConfigured;

final class ServiceDbConfiguredFactory
{
    public const TENANT_ID = '0d10455e-f6aa-4f31-8143-fea667d561af';

    public const SERVICE_NAME = 'service_name';

    public static function getServiceDbConfigured(
        string $tenantId = self::TENANT_ID,
        string $serviceName = self::SERVICE_NAME,
    ): ServiceDbConfigured {
        return new ServiceDbConfigured($tenantId, $serviceName);
    }
}
