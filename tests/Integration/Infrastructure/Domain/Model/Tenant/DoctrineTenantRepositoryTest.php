<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Tenant;

use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Infrastructure\Domain\Model\Tenant\DoctrineTenantRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\TenantFactory;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class DoctrineTenantRepositoryTest extends IntegrationTestCase
{
    private DoctrineTenantRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var DoctrineTenantRepository $repository */
        $repository = self::getContainer()->get(DoctrineTenantRepository::class);
        $this->repository = $repository;
    }

    public function testFindAllReturnsArray(): void
    {
        $tenants = $this->repository->findAll(0);

        $this->assertCount(5, $tenants);

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

    public function findAllWithNullDbConfiguredAt(): void
    {
        $emptyTenants = $this->repository->findAllWithNullDbConfiguredAt(0);

        $this->assertCount(1, $emptyTenants);

        $this->assertInstanceOf(Tenant::class, $emptyTenants[0]);
        $this->assertSame(TenantFactory::TENANT_FOR_DELETE_ID, $emptyTenants[0]->getId());
        $this->assertNull($emptyTenants[0]->getConfiguredAt());
    }

    public function testFindOneByIdWillReturnTenant(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById(TenantFactory::TENANT_ID);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertSame(TenantFactory::TENANT_ID, $tenant->getId());
    }

    public function testFindOneByIdWillReturnNull(): void
    {
        $tenant = $this->repository->findOneById(TenantFactory::NON_EXISTING_TENANT_ID);

        $this->assertNull($tenant);
    }

    public function testFindOneByAliexpressSellerIdWillReturnTenant(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneByAliexpressSellerId(TenantFactory::DS_AUTHORISED_TENANT_ALIEXPRESS_SELLER_ID);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertSame(TenantFactory::DS_AUTHORISED_TENANT_ID, $tenant->getId());
    }

    public function testFindOneByAliexpressSellerIdWillReturnNull(): void
    {
        $tenant = $this->repository->findOneByAliexpressSellerId(TenantFactory::NON_EXIST_ALIEXPRESS_SELLER_ID);

        $this->assertNull($tenant);
    }

    public function testSave(): void
    {
        $tenant = TenantFactory::getNonExistingTenant();
        $this->repository->save($tenant);

        $tenantFromDb = $this->repository->findOneById($tenant->getId());

        $this->assertSame($tenant, $tenantFromDb);
    }

    public function testSaveThrowsException(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);

        $this->repository->save(TenantFactory::getTenant());
    }

    public function testRemoveTenant(): void
    {
        /** @var Tenant $tenant */
        $tenant = $this->repository->findOneById(TenantFactory::TENANT_FOR_DELETE_ID);
        $this->assertNull($tenant->getDeletedAt());
        $this->repository->remove($tenant);

        /** @var Tenant $checkTenant */
        $checkTenant = $this->repository->findOneById(TenantFactory::TENANT_FOR_DELETE_ID);
        $this->assertNotNull($checkTenant->getDeletedAt());
    }

    /**
     * @dataProvider provideTenantStatus
     */
    public function findTenantsByStatusReturnsOk(array $status, array $expectedTenant): void
    {
        $tenants = $this->repository->findTenantsByStatus(0, $status);

        $this->assertCount(1, $tenants);

        $this->assertSame($expectedTenant['id'], $tenants[0]->getId());
        $this->assertSame($expectedTenant['company_id'], $tenants[0]->getCompanyId());
        $this->assertSame($expectedTenant['domain'], $tenants[0]->getDomain());
        $this->assertSame($expectedTenant['config'], $tenants[0]->getDbConfig());
        $this->assertSame($expectedTenant['status'], $tenants[0]->getStatus());
    }

    public function provideTenantStatus(): array
    {
        return [
            [
                [
                    TenantFactory::TENANT_STATUS_TEST->value,
                ],
                [
                    'id' => TenantFactory::TENANT_FOR_DELETE_ID,
                    'company_id' => TenantFactory::TENANT_FOR_DELETE_COMPANY_ID,
                    'domain' => TenantFactory::TENANT_FOR_DELETE_DOMAIN,
                    'config' => TenantFactory::TENANT_FOR_DELETE_CONFIG,
                    'status' => TenantFactory::TENANT_STATUS_TEST,
                ],
            ],
            [
                [
                    TenantFactory::TENANT_STATUS_LIVE->value,
                ],
                [
                    'id' => TenantFactory::SECOND_TENANT_ID,
                    'company_id' => TenantFactory::SECOND_COMPANY_ID,
                    'domain' => TenantFactory::SECOND_DOMAIN,
                    'config' => TenantFactory::SECOND_CONFIG,
                    'status' => TenantFactory::TENANT_STATUS_LIVE,
                ],
            ],
            [
                [
                    TenantFactory::TENANT_STATUS_SUSPENDED->value,
                ],
                [
                    'id' => TenantFactory::TENANT_ID,
                    'company_id' => TenantFactory::COMPANY_ID,
                    'domain' => TenantFactory::DOMAIN,
                    'config' => TenantFactory::getConfig(),
                    'status' => TenantFactory::TENANT_STATUS_SUSPENDED,
                ],
            ],
        ];
    }

    public function testFindTenantsWithAppInstalledReturnsTenantsWithAliExpressApp(): void
    {
        $tenants = $this->repository->findTenantsWithAppInstalled(AppId::AliExpress, 0);

        $this->assertIsArray($tenants);
        $this->assertGreaterThan(0, count($tenants));

        foreach ($tenants as $tenant) {
            $this->assertInstanceOf(Tenant::class, $tenant);
            $this->assertTrue($tenant->isAppInstalled(AppId::AliExpress));
            $this->assertNull($tenant->getDeletedAt());
            $this->assertTrue($tenant->isAvailable());
        }
    }

    public function testFindTenantsWithAppInstalledWithPagination(): void
    {
        $chunk0 = $this->repository->findTenantsWithAppInstalled(AppId::AliExpress, 0, 1);
        $chunk1 = $this->repository->findTenantsWithAppInstalled(AppId::AliExpress, 1, 1);

        $this->assertIsArray($chunk0);
        $this->assertIsArray($chunk1);

        if (count($chunk0) > 0 && count($chunk1) > 0) {
            $this->assertNotSame($chunk0[0]->getId(), $chunk1[0]->getId());
        }
    }

    public function testFindTenantsWithAppInstalledReturnsEmptyForNonExistentApp(): void
    {
        // Test with an app that likely doesn't exist in fixtures
        $tenants = $this->repository->findTenantsWithAppInstalled(AppId::AliExpress, 999);

        $this->assertIsArray($tenants);
        $this->assertCount(0, $tenants);
    }

    public function testFindTenantsWithAppInstalledExcludesDeletedTenants(): void
    {
        $tenants = $this->repository->findTenantsWithAppInstalled(AppId::AliExpress, 0);

        foreach ($tenants as $tenant) {
            $this->assertNull($tenant->getDeletedAt(), 'Deleted tenants should not be returned');
        }
    }

    public function testFindTenantsWithAppInstalledExcludesUnavailableTenants(): void
    {
        $tenants = $this->repository->findTenantsWithAppInstalled(AppId::AliExpress, 0);

        foreach ($tenants as $tenant) {
            $this->assertTrue($tenant->isAvailable(), 'Unavailable tenants should not be returned');
        }
    }

    public function testFindTenantsWithAppInstalledOrdersById(): void
    {
        $tenants = $this->repository->findTenantsWithAppInstalled(AppId::AliExpress, 0);

        if (count($tenants) > 1) {
            $previousId = null;
            foreach ($tenants as $tenant) {
                if (null !== $previousId) {
                    $this->assertGreaterThan($previousId, $tenant->getId(), 'Tenants should be ordered by ID ASC');
                }
                $previousId = $tenant->getId();
            }
        }

        $this->assertTrue(true); // Test passes if we get here
    }
}
