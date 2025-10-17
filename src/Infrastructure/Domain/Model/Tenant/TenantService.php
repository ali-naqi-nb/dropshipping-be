<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Tenant;

use App\Application\Service\Encryption\EncryptorInterface;
use App\Domain\Model\Tenant\DbConfig;
use App\Domain\Model\Tenant\DropshippingDbCreated;
use App\Domain\Model\Tenant\ServiceDbConfigured;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantConfigUpdated;
use App\Domain\Model\Tenant\TenantDeleted;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Domain\Model\Tenant\TenantStatusUpdated;
use DateInterval;
use Exception;
use NextBasket\ProcessPoolBundle\Events\ProcessFinished;
use NextBasket\ProcessPoolBundle\Events\ProcessStarted;
use NextBasket\ProcessPoolBundle\ProcessPool;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplObjectStorage;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class TenantService implements TenantServiceInterface
{
    private const MIGRATIONS_CONFIG = 'config/packages/migrations/tenant.yaml';
    private const DB_CONFIG_CACHE_EXPIRATION_INTERVAL = 'PT1H';
    private const AVAILABILITY_CACHE_EXPIRATION_INTERVAL = 'PT1H';
    private const COMPANY_ID_CACHE_EXPIRATION_INTERVAL = 'PT1H';
    private const DB_MIGRATIONS_TIMEOUT = 300;

    /** @param TagAwareAdapter $cache */
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
        private readonly TagAwareCacheInterface $cache,
        private readonly MessageBusInterface $messageBus,
        private readonly EncryptorInterface $encryptor,
        private readonly LoggerInterface $logger,
        private readonly string $dbEncryptionKey,
        private readonly string $appServiceName,
    ) {
    }

    /**
     * @return Tenant[]
     */
    public function getAll(int $chunk, int $chunkSize = self::CHUNK_SIZE): array
    {
        return $this->repository->findAll($chunk, $chunkSize);
    }

    public function update(TenantConfigUpdated $event): void
    {
        $tenant = $this->repository->findOneById($event->getTenantId());

        if (null === $tenant) {
            $this->logger->critical(
                sprintf('Tenant update error (tenant with id %s not exists).', $event->getTenantId())
            );

            return;
        }

        $tenant->setDefaultLanguage($event->getDefaultLanguage());
        $tenant->setDefaultCurrency($event->getDefaultCurrency());
        $this->repository->save($tenant);
    }

    /**
     * @return Tenant[]
     */
    public function getTenantsByStatus(int $chunk, array $status = [], int $chunkSize = self::CHUNK_SIZE): array
    {
        return $this->repository->findTenantsByStatus($chunk, $status, $chunkSize);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(DropshippingDbCreated $event): void
    {
        $tenant = $this->repository->findOneById($event->getTenantId());
        if (null === $tenant && false === $event->isDbCreated()) {
            $tenant = new Tenant(
                id: $event->getTenantId(),
                companyId: $event->getCompanyId(),
                domain: $event->getDomain(),
                dbConfig: $event->getConfig(),
                defaultLanguage: $event->getDefaultLanguage(),
                defaultCurrency: $event->getDefaultCurrency(),
                status: $event->getStatus(),
            );
            $this->repository->save($tenant);
        } else {
            $this->logger->warning(
                sprintf('Tenant creation warning (tenant with id %s already exists).', $event->getTenantId())
            );
        }

        if (null !== $tenant && $event->isDbCreated()) {
            $tenant->setDbConfig($event->getConfig());
            $this->repository->save($tenant);

            $cacheItem = $this->cache->getItem($this->getDbConfigCacheKey($event->getTenantId()));
            $cacheItem->set($event->getConfig())
                ->expiresAfter(new DateInterval(self::DB_CONFIG_CACHE_EXPIRATION_INTERVAL));
            $this->cache->save($cacheItem);

            $successDbMigration = $this->executeDbMigrations($event->getTenantId());

            // @codeCoverageIgnoreStart
            if (!$successDbMigration) {
                $tenant->setConfiguredAt(null);
                $this->repository->save($tenant);
                throw new RuntimeException('Tenant migrations error.');
            }
            // @codeCoverageIgnoreEnd

            if ($tenant->isAvailable()) {
                $this->messageBus->dispatch(new ServiceDbConfigured($tenant->getId(), $this->appServiceName));
            }
        }
    }

    public function executeDbMigrations(string $tenantId): bool
    {
        if (null === $this->repository->findOneById($tenantId)) {
            $this->logger->error('Tenant migrations error', ['tenantId' => $tenantId, 'error' => 'Invalid tenant id']);

            return false;
        }

        $this->makeUnavailable($tenantId);

        $process = new Process([
            'bin/console',
            'doctrine:migrations:migrate',
            '--configuration='.self::MIGRATIONS_CONFIG,
            '--no-interaction',
            '--allow-no-migration',
            '--all-or-nothing',
            '--tenant='.$tenantId,
        ]);
        $process->run();

        if ($process->isSuccessful()) {
            $this->makeAvailable($tenantId);

            return true;
        }

        // @codeCoverageIgnoreStart
        $this->logger->error(
            'Tenant migrations error',
            ['tenantId' => $tenantId, 'error' => $process->getErrorOutput()]
        );

        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param Tenant[] $tenants
     */
    public function executeParallelDbMigrations(array $tenants, int $concurrency = self::CONCURRENCY): void
    {
        if (!count($tenants)) {
            return;
        }

        $processes = new SplObjectStorage();
        $tenantsMap = [];

        foreach ($tenants as $tenant) {
            if (null === $tenant->getConfiguredAt()) {
                continue;
            }

            $process = new Process(command: [
                'bin/console',
                'doctrine:migrations:migrate',
                '--configuration='.self::MIGRATIONS_CONFIG,
                '--no-interaction',
                '--allow-no-migration',
                '--all-or-nothing',
                '--tenant='.$tenant->getId(),
            ], timeout: self::DB_MIGRATIONS_TIMEOUT);

            $processes->attach($process);
            $tenantsMap[spl_object_hash($process)] = $tenant;
        }

        // SplObjectStorage implements Iterator interface but phpstan throws an error
        /** @phpstan-ignore-next-line */
        $processPool = new ProcessPool($processes);
        $processPool->setConcurrency($concurrency);

        $processPool->onProcessStarted(function (ProcessStarted $event) use ($tenantsMap) {
            $process = $event->getProcess();
            $hash = spl_object_hash($process);
            isset($tenantsMap[$hash]) && $this->makeUnavailable(target: $tenantsMap[$hash]);
        });

        $processPool->onProcessFinished(function (ProcessFinished $event) use ($tenantsMap) {
            $process = $event->getProcess();
            if (0 !== $process->getExitCode()) {
                $this->logger->error(
                    'Tenant migrations error',
                    ['command' => $process->getCommandLine(), 'error' => $process->getErrorOutput()]
                );
            } else {
                $this->logger->info($process->getOutput());
            }

            $hash = spl_object_hash($process);
            isset($tenantsMap[$hash]) && $this->makeAvailable(target: $tenantsMap[$hash]);
        });
        $processPool->wait();
    }

    public function isAvailable(string $tenantId): bool
    {
        $cacheItem = $this->cache->getItem($this->getAvailabilityCacheKey($tenantId));

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $tenant = $this->repository->findOneById($tenantId);
        if (null !== $tenant) {
            $cacheItem->set($tenant->isAvailable());
            $cacheItem->expiresAfter(new DateInterval(self::AVAILABILITY_CACHE_EXPIRATION_INTERVAL));
            $this->cache->save($cacheItem);

            return $cacheItem->get();
        }

        return false;
    }

    public function getDbConfig(string $tenantId): ?DbConfig
    {
        $cacheItem = $this->cache->getItem($this->getDbConfigCacheKey($tenantId));
        if (!$cacheItem->isHit()) {
            $tenant = $this->repository->findOneById($tenantId);

            if (null === $tenant) {
                return null;
            }
            $cacheItem->set($tenant->getDbConfig());
            $cacheItem->expiresAfter(new DateInterval(self::DB_CONFIG_CACHE_EXPIRATION_INTERVAL));
            $this->cache->save($cacheItem);
        }

        $dbConfigString = $this->encryptor->decrypt($cacheItem->get(), $this->dbEncryptionKey);

        return DbConfig::fromString($tenantId, $dbConfigString);
    }

    public function getCompanyId(string $tenantId): ?string
    {
        $cacheItem = $this->cache->getItem($this->getCompanyCacheKey($tenantId));
        if (!$cacheItem->isHit()) {
            $tenant = $this->repository->findOneById($tenantId);

            if (null === $tenant) {
                return null;
            }
            $cacheItem->set($tenant->getCompanyId());
            $cacheItem->expiresAfter(new DateInterval(self::COMPANY_ID_CACHE_EXPIRATION_INTERVAL));
            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeTenant(TenantDeleted $event): void
    {
        $tenant = $this->repository->findOneById($event->getTenantId());
        if (null !== $tenant) {
            $this->repository->remove($tenant);
            $this->cache->deleteItem($this->getAvailabilityCacheKey($tenant->getId()));
            $this->cache->deleteItem($this->getDbConfigCacheKey($tenant->getId()));
        }
    }

    public function removeTenantById(Tenant $tenant): void
    {
        $this->repository->remove($tenant);
        $this->cache->deleteItem($this->getAvailabilityCacheKey($tenant->getId()));
        $this->cache->deleteItem($this->getDbConfigCacheKey($tenant->getId()));
    }

    private function getTenant(string $tenantId): ?Tenant
    {
        return $this->repository->findOneById($tenantId);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function makeUnavailable(Tenant|string $target): void
    {
        $tenant = $target instanceof Tenant
            ? $target
            : $this->getTenant($target);

        if (null !== $tenant) {
            $tenant->makeUnavailable();
            $this->repository->save($tenant);
            $cacheItem = $this->cache->getItem($this->getAvailabilityCacheKey($tenant->getId()));
            $cacheItem->set(false);
            $cacheItem->expiresAfter(new DateInterval(self::AVAILABILITY_CACHE_EXPIRATION_INTERVAL));
            $this->cache->save($cacheItem);
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function makeAvailable(Tenant|string $target): void
    {
        $tenant = $target instanceof Tenant
            ? $target
            : $this->getTenant($target);

        if (null !== $tenant) {
            $tenant->makeAvailable();
            $this->repository->save($tenant);
            $cacheItem = $this->cache->getItem($this->getAvailabilityCacheKey($tenant->getId()));
            $cacheItem->set(true);
            $cacheItem->expiresAfter(new DateInterval(self::AVAILABILITY_CACHE_EXPIRATION_INTERVAL));
            $this->cache->save($cacheItem);
        }
    }

    private function getAvailabilityCacheKey(string $tenantId): string
    {
        return $this->appServiceName.'_tenant_availability_'.$tenantId;
    }

    private function getDbConfigCacheKey(string $tenantId): string
    {
        return $this->appServiceName.'_tenant_db_'.$tenantId;
    }

    public function getCompanyCacheKey(string $tenantId): string
    {
        return $this->appServiceName.'_tenant_company_id_'.$tenantId;
    }

    public function updateStatus(TenantStatusUpdated $event): void
    {
        /** @var Tenant|null $tenant */
        $tenant = $this->repository->findOneById($event->getTenantId());

        if (null === $tenant) {
            $this->logger->critical(
                sprintf('Tenant status updating error (tenant with id %s not exists).', $event->getTenantId())
            );

            return;
        }

        $tenant->setStatus($event->getStatus());
        $this->repository->save($tenant);
    }

    public function getAllWithNullDbConfiguredAt(int $chunk, int $chunkSize = self::CHUNK_SIZE): array
    {
        return $this->repository->findAllWithNullDbConfiguredAt($chunk, $chunkSize);
    }
}
