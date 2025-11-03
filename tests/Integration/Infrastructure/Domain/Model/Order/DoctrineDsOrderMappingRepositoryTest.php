<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Order;

use App\Domain\Model\Order\DsOrderMapping;
use App\Infrastructure\Domain\Model\Order\DoctrineDsOrderMappingRepository;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\DsOrderMappingFactory as Factory;
use Doctrine\DBAL\Exception;

final class DoctrineDsOrderMappingRepositoryTest extends IntegrationTestCase
{
    private DoctrineDsOrderMappingRepository $repository;
    private DoctrineTenantConnection $connection;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createDoctrineTenantConnection();

        /** @var DoctrineDsOrderMappingRepository $repository */
        $repository = self::getContainer()->get(DoctrineDsOrderMappingRepository::class);
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }

    public function testFindNextId(): void
    {
        $nextId = $this->repository->findNextId();

        $this->assertIsString($nextId);
        $this->assertNotEmpty($nextId);
        $this->assertMatchesPattern('@uuid@', $nextId);
    }

    public function testFindOneByIdReturnsDsOrderMapping(): void
    {
        $dsOrderMapping = $this->repository->findOneById(Factory::FIRST_ORDER_ID);

        $this->assertInstanceOf(DsOrderMapping::class, $dsOrderMapping);
        $this->assertSame(Factory::FIRST_ORDER_ID, $dsOrderMapping->getId());
    }

    public function testFindOneByIdReturnsNull(): void
    {
        $dsOrderMapping = $this->repository->findOneById(Factory::NON_EXISTING_ORDER_ID);

        $this->assertNull($dsOrderMapping);
    }

    public function testFindOneByDsOrderIdReturnsDsOrderMapping(): void
    {
        $dsOrderMapping = $this->repository->findOneByDsOrderId(Factory::FIRST_ORDER_DS_ORDER_ID);

        $this->assertInstanceOf(DsOrderMapping::class, $dsOrderMapping);
        $this->assertSame(Factory::FIRST_ORDER_DS_ORDER_ID, $dsOrderMapping->getDsOrderId());
    }

    public function testFindOneByNonExistDsOrderIdIdReturnsNull(): void
    {
        $dsOrderMapping = $this->repository->findOneByDsOrderId(Factory::NEW_ORDER_DS_ORDER_ID);

        $this->assertNull($dsOrderMapping);
    }

    public function testSaveIsSuccessful(): void
    {
        $dsOrderMapping = Factory::createDsOrderMapping(
            id: Factory::NEW_ORDER_ID,
            nbOrderId: Factory::NEW_ORDER_NB_ORDER_ID,
            dsOrderId: Factory::NEW_ORDER_DS_ORDER_ID,
            dsProvider: Factory::NEW_ORDER_DS_PROVIDER,
            dsStatus: Factory::NEW_ORDER_DS_STATUS
        );

        $this->assertNull($dsOrderMapping->getCreatedAt());
        $this->assertNull($dsOrderMapping->getUpdatedAt());

        $this->repository->save($dsOrderMapping);

        $savedDsOrderMapping = $this->repository->findOneById(Factory::NEW_ORDER_ID);
        $this->assertNotNull($savedDsOrderMapping);

        $this->assertSame(Factory::NEW_ORDER_ID, $savedDsOrderMapping->getId());
        $this->assertSame(Factory::NEW_ORDER_NB_ORDER_ID, $savedDsOrderMapping->getNbOrderId());
        $this->assertSame(Factory::NEW_ORDER_DS_ORDER_ID, $savedDsOrderMapping->getDsOrderId());
        $this->assertSame(Factory::NEW_ORDER_DS_PROVIDER, $savedDsOrderMapping->getDsProvider());
        $this->assertSame(Factory::NEW_ORDER_DS_STATUS, $savedDsOrderMapping->getDsStatus());
        $this->assertNotNull($savedDsOrderMapping->getCreatedAt());
        $this->assertNotNull($savedDsOrderMapping->getUpdatedAt());
    }

    public function testDeleteIsSuccessful(): void
    {
        $dsOrderMapping = $this->repository->findOneById(Factory::FIRST_ORDER_ID);
        $this->assertNotNull($dsOrderMapping);

        $this->repository->delete($dsOrderMapping);

        $dsOrderMapping = $this->repository->findOneById(Factory::FIRST_ORDER_ID);
        $this->assertNull($dsOrderMapping);
    }
}
