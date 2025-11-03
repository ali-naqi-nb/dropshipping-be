<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\Tenant;

use App\Application\EventHandler\Tenant\TenantStatusUpdatedEventHandler;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantStatusUpdated;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\TenantFactory;

final class TenantStatusUpdatedEventHandlerTest extends IntegrationTestCase
{
    private TenantStatusUpdatedEventHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TenantStatusUpdatedEventHandler $handler */
        $handler = self::getContainer()->get(TenantStatusUpdatedEventHandler::class);
        $this->handler = $handler;
    }

    public function testInvokeWithCorrectData(): void
    {
        $event = new TenantStatusUpdated(
            TenantFactory::TENANT_ID,
            TenantFactory::TENANT_STATUS_TEST->value
        );

        /** @var TenantRepositoryInterface $tenantRepository */
        $tenantRepository = self::getContainer()->get(TenantRepositoryInterface::class);
        /** @var Tenant $tenant */
        $tenant = $tenantRepository->findOneById($event->getTenantId());

        $this->assertNotSame($event->getStatus(), $tenant->getStatus());

        $this->handler->__invoke($event);

        $this->assertSame($event->getStatus(), $tenant->getStatus());
    }

    public function testInvokeWithNonExistingTenant(): void
    {
        $event = new TenantStatusUpdated(
            TenantFactory::NON_EXISTING_TENANT_ID,
            TenantFactory::TENANT_STATUS_TEST->value
        );

        $this->handler->__invoke($event);

        $this->assertLog(
            'CRITICAL',
            sprintf('Tenant status updating error (tenant with id %s not exists).', $event->getTenantId()),
        );
    }
}
