<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Connection;

use App\Domain\Model\Tenant\TenantStorageInterface;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Traits\RedisProxy;

final class RedisTenantConnection extends RedisTagAwareAdapter
{
    private ?string $namespace = null;

    /**
     * @param RedisProxy $redis The redis client
     */
    public function __construct(private readonly RedisProxy $redis, private readonly TenantStorageInterface $tenantStorage)
    {
    }

    public function connect(): void
    {
        $tenantId = (string) $this->tenantStorage->getId();
        if ($tenantId !== $this->namespace) {
            parent::__construct($this->redis, $tenantId);
            $this->namespace = $tenantId;
        }
    }
}
