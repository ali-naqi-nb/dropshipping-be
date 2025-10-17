<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Domain\Model\Order\DsOrderMapping;
use App\Tests\Unit\UnitTestCase;
use Datetime;
use Symfony\Component\Uid\Uuid;

final class DsOrderMappingTest extends UnitTestCase
{
    public function testConstructorAndGetters(): void
    {
        $id = Uuid::v4()->toRfc4122();
        $nbOrderId = Uuid::v4()->toRfc4122();
        $dsOrderId = 'DS123456';
        $dsProvider = 'ProviderX';
        $dsStatus = 'pending';
        $dsOrderMapping = new DsOrderMapping($id, $nbOrderId, $dsOrderId, $dsProvider, $dsStatus);

        $this->assertSame($id, $dsOrderMapping->getId());
        $this->assertSame($nbOrderId, $dsOrderMapping->getNbOrderId());
        $this->assertSame($dsOrderId, $dsOrderMapping->getDsOrderId());
        $this->assertSame($dsProvider, $dsOrderMapping->getDsProvider());
        $this->assertSame($dsStatus, $dsOrderMapping->getDsStatus());
        $this->assertNull($dsOrderMapping->getCreatedAt());
        $this->assertNull($dsOrderMapping->getUpdatedAt());
    }

    public function testSetDsStatus(): void
    {
        $id = Uuid::v4()->toRfc4122();
        $nbOrderId = Uuid::v4()->toRfc4122();
        $dsOrderId = 'DS123456';
        $dsProvider = 'ProviderX';
        $dsStatus = 'pending';
        $dsOrderMapping = new DsOrderMapping($id, $nbOrderId, $dsOrderId, $dsProvider, $dsStatus);

        $newStatus = 'Completed';
        $dsOrderMapping->setDsStatus($newStatus);

        $this->assertSame($newStatus, $dsOrderMapping->getDsStatus());
    }

    public function testSetDates(): void
    {
        $id = Uuid::v4()->toRfc4122();
        $nbOrderId = Uuid::v4()->toRfc4122();
        $dsOrderId = 'DS123456';
        $dsProvider = 'ProviderX';
        $dsStatus = 'pending';
        $dsOrderMapping = new DsOrderMapping($id, $nbOrderId, $dsOrderId, $dsProvider, $dsStatus);

        $newCreatedAt = new DateTime('2024-01-01 12:00:00');
        $dsOrderMapping->setCreatedAt($newCreatedAt);

        $newUpdatedAt = new DateTime('2024-01-01 12:00:00');
        $dsOrderMapping->setUpdatedAt($newUpdatedAt);

        $this->assertSame($newCreatedAt, $dsOrderMapping->getCreatedAt());
        $this->assertSame($newUpdatedAt, $dsOrderMapping->getUpdatedAt());
    }
}
