<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Tenant;

use App\Domain\Model\Tenant\DbConfig;
use App\Domain\Model\Tenant\ServiceDbConfigured;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\DbCreatedFactory;
use App\Tests\Shared\Factory\TenantConfigUpdatedFactory;
use App\Tests\Shared\Factory\TenantDeletedFactory;
use App\Tests\Shared\Factory\TenantFactory;
use App\Tests\Shared\Factory\TenantStatusUpdatedFactory;
use DateInterval;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class TenantServiceTest extends IntegrationTestCase
{
    private TenantServiceInterface $service;
    private TenantRepositoryInterface $repository;
    private InMemoryTransport $messenger;
    private TagAwareAdapter $cache;
    private string $serviceName;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TenantServiceInterface $service */
        $service = self::getContainer()->get(TenantServiceInterface::class);
        $this->service = $service;
        /** @var TenantRepositoryInterface $repository */
        $repository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->repository = $repository;
        /** @var InMemoryTransport $messenger */
        $messenger = self::getContainer()->get('messenger.transport.async_configured_service_db');
        $this->messenger = $messenger;
        /** @var TagAwareAdapter $cache */
        $cache = self::getContainer()->get(TagAwareCacheInterface::class);
        $this->cache = $cache;

        /** @var string $serviceName */
        $serviceName = self::getContainer()->getParameter('app.service_name');
        $this->serviceName = $serviceName;

        $this->cache->deleteItem($this->serviceName.'_tenant_db_'.TenantFactory::TENANT_ID);
        $this->cache->deleteItem($this->serviceName.'_tenant_availability_'.TenantFactory::TENANT_ID);
        $this->cache->deleteItem($this->serviceName.'_tenant_company_id_'.TenantFactory::TENANT_ID);
        $this->cache->deleteItem($this->serviceName.'_tenant_db_'.TenantFactory::NON_EXISTING_TENANT_ID);
        $this->cache->deleteItem($this->serviceName.'_tenant_availability_'.TenantFactory::NON_EXISTING_TENANT_ID);
        $this->cache->deleteItem($this->serviceName.'_tenant_company_id_'.TenantFactory::NON_EXISTING_TENANT_ID);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::getContainer()->reset();
    }

    public function testUpdateNonExistingTenant(): void
    {
        $event = TenantConfigUpdatedFactory::getNonExistingTenantConfigUpdated();
        $this->service->update($event);
        $this->assertNull($this->repository->findOneById($event->getTenantId()));
        $this->assertLog(
            'CRITICAL',
            sprintf('Tenant update error (tenant with id %s not exists).', $event->getTenantId()),
            []
        );
    }

    public function testUpdate(): void
    {
        $event = TenantConfigUpdatedFactory::getConfigUpdated();
        $this->service->update($event);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($event->getTenantId());
        $this->assertSame($event->getDefaultLanguage(), $tenant->getDefaultLanguage());
        $this->assertSame($event->getDefaultCurrency(), $tenant->getDefaultCurrency());
        $this->assertInstanceOf(Tenant::class, $tenant);
    }

    public function testGetAllReturnsArray(): void
    {
        $tenants = $this->service->getAll(0);

        $this->assertSame(TenantFactory::TENANT_FOR_DELETE_ID, $tenants[0]->getId());
        $this->assertSame(TenantFactory::TENANT_FOR_DELETE_COMPANY_ID, $tenants[0]->getCompanyId());
        $this->assertSame(TenantFactory::TENANT_FOR_DELETE_DOMAIN, $tenants[0]->getDomain());
        $this->assertSame(TenantFactory::TENANT_FOR_DELETE_CONFIG, $tenants[0]->getDbConfig());
        $this->assertSame(TenantFactory::TENANT_FOR_DELETE_LANGUAGE, $tenants[0]->getDefaultLanguage());
        $this->assertSame(TenantFactory::TENANT_FOR_DELETE_CURRENCY, $tenants[0]->getDefaultCurrency());
        $this->assertSame(TenantFactory::TENANT_STATUS_TEST, $tenants[0]->getStatus());

        $this->assertSame(TenantFactory::SECOND_TENANT_ID, $tenants[1]->getId());
        $this->assertSame(TenantFactory::SECOND_COMPANY_ID, $tenants[1]->getCompanyId());
        $this->assertSame(TenantFactory::SECOND_DOMAIN, $tenants[1]->getDomain());
        $this->assertSame(TenantFactory::SECOND_CONFIG, $tenants[1]->getDbConfig());
        $this->assertSame(TenantFactory::SECOND_LANGUAGE, $tenants[1]->getDefaultLanguage());
        $this->assertSame(TenantFactory::SECOND_CURRENCY, $tenants[1]->getDefaultCurrency());
        $this->assertSame(TenantFactory::TENANT_STATUS_LIVE, $tenants[1]->getStatus());

        $this->assertSame(TenantFactory::TENANT_ID, $tenants[2]->getId());
        $this->assertSame(TenantFactory::COMPANY_ID, $tenants[2]->getCompanyId());
        $this->assertSame(TenantFactory::DOMAIN, $tenants[2]->getDomain());
        $this->assertSame(TenantFactory::getConfig(), $tenants[2]->getDbConfig());
        $this->assertSame(TenantFactory::DEFAULT_LANGUAGE, $tenants[2]->getDefaultLanguage());
        $this->assertSame(TenantFactory::DEFAULT_CURRENCY, $tenants[2]->getDefaultCurrency());
        $this->assertSame(TenantFactory::TENANT_STATUS_SUSPENDED, $tenants[2]->getStatus());
    }

    public function testCreateWithNewTenant(): void
    {
        $dbCreated = DbCreatedFactory::getNonExistingDbCreated();

        $this->assertNull($this->repository->findOneById($dbCreated->getTenantId()));

        $this->service->create($dbCreated);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($dbCreated->getTenantId());
        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertFalse($tenant->isAvailable());

        $this->assertCount(0, $this->messenger->getSent());
    }

    public function testExecuteDbMigrationsWithInvalidTenantId(): void
    {
        $this->service->executeDbMigrations(TenantFactory::COMPANY_ID);

        $this->assertFalse($this->service->isAvailable(TenantFactory::COMPANY_ID));
    }

    public function testExecuteParallelDbMigrationsWithValidTenants(): void
    {
        $dbCreated = DbCreatedFactory::getDbCreated();
        $tenant = $this->repository->findOneById($dbCreated->getTenantId());
        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->service->executeParallelDbMigrations([$tenant]);
        $this->assertTrue($this->service->isAvailable($dbCreated->getTenantId()));
    }

    public function testCreateWithExistingTenant(): void
    {
        $dbCreated = DbCreatedFactory::getDbCreated();

        $this->assertInstanceOf(Tenant::class, $this->repository->findOneById($dbCreated->getTenantId()));

        $this->service->create($dbCreated);

        $this->assertLog(
            'WARNING',
            sprintf('Tenant creation warning (tenant with id %s already exists).', $dbCreated->getTenantId())
        );
    }

    public function testCreateWithExistingTenantWithDbCreatedTrue(): void
    {
        $dbCreated = DbCreatedFactory::getDbCreated(dbCreated: true);

        $this->assertInstanceOf(Tenant::class, $this->repository->findOneById($dbCreated->getTenantId()));

        $this->service->create($dbCreated);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($dbCreated->getTenantId());

        $this->assertTrue($tenant->isAvailable());
        $this->assertCount(1, $this->messenger->getSent());
        $this->assertInstanceOf(ServiceDbConfigured::class, $this->messenger->getSent()[0]->getMessage());
    }

    public function testGetDbConfigForExistingTenant(): void
    {
        $this->assertInstanceOf(DbConfig::class, $this->service->getDbConfig(TenantFactory::TENANT_ID));
        $this->assertInstanceOf(DbConfig::class, $this->service->getDbConfig(TenantFactory::TENANT_ID)); // use cache
    }

    public function testGetDbConfigForNonExistingTenant(): void
    {
        $this->assertNull($this->service->getDbConfig(TenantFactory::NON_EXISTING_TENANT_ID));
        $this->assertNull($this->service->getDbConfig(TenantFactory::NON_EXISTING_TENANT_ID)); // use cache
    }

    public function testIsAvailableForExistingTenant(): void
    {
        $this->assertTrue($this->service->isAvailable(TenantFactory::TENANT_ID));
        $this->assertTrue($this->service->isAvailable(TenantFactory::TENANT_ID)); // use cache
    }

    public function testIsAvailableForNonExistingTenant(): void
    {
        $this->assertFalse($this->service->isAvailable(TenantFactory::NON_EXISTING_TENANT_ID));
        $this->assertFalse($this->service->isAvailable(TenantFactory::NON_EXISTING_TENANT_ID)); // use cache
    }

    public function testGetCompanyIdForExistingTenant(): void
    {
        $this->assertIsString($this->service->getCompanyId(TenantFactory::TENANT_ID));
        $this->assertIsString($this->service->getCompanyId(TenantFactory::TENANT_ID)); // use cache
    }

    public function testGetCompanyIdForNonExistingTenant(): void
    {
        $this->assertNull($this->service->getCompanyId(TenantFactory::NON_EXISTING_TENANT_ID));
        $this->assertNull($this->service->getCompanyId(TenantFactory::NON_EXISTING_TENANT_ID)); // use cache
    }

    public function testRemoveTenantWithExistingTenant(): void
    {
        $availabilityKey = $this->serviceName.'_tenant_availability_'.TenantFactory::TENANT_FOR_DELETE_ID;
        $dbConfigKey = $this->serviceName.'_tenant_db_'.TenantFactory::TENANT_FOR_DELETE_ID;

        $item = $this->cache->getItem($availabilityKey);
        $item->set(true);
        $item->expiresAfter(new DateInterval('PT1H'));
        $this->cache->save($item);

        $itemDb = $this->cache->getItem($dbConfigKey);
        $itemDb->set(TenantFactory::TENANT_FOR_DELETE_CONFIG);
        $itemDb->expiresAfter(new DateInterval('PT1H'));
        $this->cache->save($itemDb);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById(TenantFactory::TENANT_FOR_DELETE_ID);
        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertNull($tenant->getDeletedAt());

        $this->service->removeTenant(TenantDeletedFactory::getTenantDeleted());

        /** @var Tenant $checkTenant */
        $checkTenant = $this->repository->findOneById(TenantFactory::TENANT_FOR_DELETE_ID);
        $this->assertInstanceOf(Tenant::class, $checkTenant);
        $this->assertNotNull($checkTenant->getDeletedAt());

        $this->assertFalse($this->cache->getItem($availabilityKey)->isHit());
        $this->assertFalse($this->cache->getItem($dbConfigKey)->isHit());
    }

    public function testRemoveTenantByIdWithExistingTenant(): void
    {
        $availabilityKey = $this->serviceName.'_tenant_availability_'.TenantFactory::TENANT_FOR_DELETE_ID;
        $dbConfigKey = $this->serviceName.'_tenant_db_'.TenantFactory::TENANT_FOR_DELETE_ID;

        $item = $this->cache->getItem($availabilityKey);
        $item->set(true);
        $item->expiresAfter(new DateInterval('PT1H'));
        $this->cache->save($item);

        $itemDb = $this->cache->getItem($dbConfigKey);
        $itemDb->set(TenantFactory::TENANT_FOR_DELETE_CONFIG);
        $itemDb->expiresAfter(new DateInterval('PT1H'));
        $this->cache->save($itemDb);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById(TenantFactory::TENANT_FOR_DELETE_ID);
        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertNull($tenant->getDeletedAt());

        $this->service->removeTenantById($tenant);

        /** @var Tenant $checkTenant */
        $checkTenant = $this->repository->findOneById(TenantFactory::TENANT_FOR_DELETE_ID);
        $this->assertInstanceOf(Tenant::class, $checkTenant);
        $this->assertNotNull($checkTenant->getDeletedAt());

        $this->assertFalse($this->cache->getItem($availabilityKey)->isHit());
        $this->assertFalse($this->cache->getItem($dbConfigKey)->isHit());
    }

    public function testUpdateStatus(): void
    {
        $event = TenantStatusUpdatedFactory::getStatusUpdated();
        $this->service->updateStatus($event);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($event->getTenantId());
        $this->assertSame($event->getStatus(), $tenant->getStatus());
        $this->assertInstanceOf(Tenant::class, $tenant);
    }

    public function testGetAllWithNullDbConfiguredAtReturnsArray(): void
    {
        $tenants = $this->service->getAllWithNullDbConfiguredAt(0);

        $this->assertInstanceOf(Tenant::class, $tenants[0]);
        $this->assertNull($tenants[0]->getConfiguredAt());

        $this->assertInstanceOf(Tenant::class, $tenants[1]);
        $this->assertNull($tenants[1]->getConfiguredAt());
    }
}
