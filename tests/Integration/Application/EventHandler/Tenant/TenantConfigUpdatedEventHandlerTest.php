<?php

namespace App\Tests\Integration\Application\EventHandler\Tenant;

use App\Application\EventHandler\Tenant\TenantConfigUpdatedEventHandler;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantConfigUpdated;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\TenantFactory;

class TenantConfigUpdatedEventHandlerTest extends IntegrationTestCase
{
    private TenantConfigUpdatedEventHandler $handler;

    private TenantRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        /** @var TenantConfigUpdatedEventHandler $handler */
        $handler = self::getContainer()->get(TenantConfigUpdatedEventHandler::class);
        $this->handler = $handler;

        /** @var TenantRepositoryInterface $repository */
        $repository = self::getContainer()->get(TenantRepositoryInterface::class);
        $this->repository = $repository;
    }

    public function testInvokeWithCorrectData(): void
    {
        $event = new TenantConfigUpdated(
            TenantFactory::TENANT_ID,
            TenantFactory::LANGUAGE_EN,
            TenantFactory::CURRENCY_EUR
        );

        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById($event->getTenantId());

        $this->assertNotSame($event->getDefaultLanguage(), $tenant->getDefaultLanguage());
        $this->handler->__invoke($event);
        $this->assertSame($event->getDefaultLanguage(), $tenant->getDefaultLanguage());
    }

    public function testInvokeWithNonExistingTenant(): void
    {
        $event = new TenantConfigUpdated(
            TenantFactory::NON_EXISTING_TENANT_ID,
            TenantFactory::LANGUAGE_EN,
            TenantFactory::CURRENCY_EUR
        );
        $this->handler->__invoke($event);

        $this->assertNull($this->repository->findOneById($event->getTenantId()));
        $this->assertLog(
            'CRITICAL',
            sprintf('Tenant update error (tenant with id %s not exists).', $event->getTenantId()),
            []
        );
    }
}
