<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Domain\Model\Tenant\DbConfig;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Infrastructure\Persistence\Connection\RedisTenantConnection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class TenantIdMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly DoctrineTenantConnection $doctrineTenantConnection,
        private readonly RedisTenantConnection $redisTenantConnection,
        private readonly TenantStorageInterface $tenantStorage,
        private readonly TenantServiceInterface $service
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var ?TenantIdStamp $tenantIdStamp */
        $tenantIdStamp = $envelope->last(TenantIdStamp::class);
        $tenantId = $this->doctrineTenantConnection->getTenantId();
        if (null !== $tenantIdStamp && $tenantId !== $tenantIdStamp->getId()) {
            /** @var DbConfig $dbConfig */
            $dbConfig = $this->service->getDbConfig($tenantIdStamp->getId());
            $this->tenantStorage->setId($tenantIdStamp->getId());
            $this->doctrineTenantConnection->create($dbConfig);
            $this->redisTenantConnection->connect();
        } elseif (null === $tenantIdStamp && null !== $tenantId) {
            $envelope = $envelope->with(new TenantIdStamp($tenantId));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
