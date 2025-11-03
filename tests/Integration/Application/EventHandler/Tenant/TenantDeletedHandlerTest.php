<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Tenant;

use App\Application\EventHandler\Tenant\TenantDeletedHandler;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\TenantDeletedFactory;
use App\Tests\Shared\Factory\TenantFactory;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class TenantDeletedHandlerTest extends IntegrationTestCase
{
    private TenantDeletedHandler $handler;

    private TenantRepositoryInterface $repository;

    private TagAwareAdapter $cache;

    public function setUp(): void
    {
        parent::setUp();

        /** @var TenantDeletedHandler $handler */
        $handler = self::getContainer()->get(TenantDeletedHandler::class);
        $this->handler = $handler;

        /** @var TenantRepositoryInterface $repository */
        $repository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->repository = $repository;

        /** @var TagAwareAdapter $cache */
        $cache = self::getContainer()->get(TagAwareCacheInterface::class);
        $this->cache = $cache;
    }

    public function testInvokeWithCorrectData(): void
    {
        $event = TenantDeletedFactory::getTenantDeleted();

        $this->handler->__invoke($event);

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById(TenantFactory::TENANT_FOR_DELETE_ID);
        $this->assertNotNull($tenant->getDeletedAt());

        $this->assertFalse($this->cache->getItem('products_tenant_availability_'.TenantFactory::TENANT_FOR_DELETE_ID)->isHit());
        $this->assertFalse($this->cache->getItem('products_tenant_db_'.TenantFactory::TENANT_FOR_DELETE_ID)->isHit());
    }
}
