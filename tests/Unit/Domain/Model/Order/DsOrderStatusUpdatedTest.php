<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Domain\Model\Order\DsOrderStatusUpdated;
use App\Domain\Model\Order\ProcessingStatus;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Uid\Uuid;

final class DsOrderStatusUpdatedTest extends UnitTestCase
{
    public function testGetters(): void
    {
        $id = Uuid::v4()->toRfc4122();

        $dsOrderStatusUpdated = new DsOrderStatusUpdated(
            nbOrderId: $id,
            nbOrderStatus: ProcessingStatus::New,
        );

        $this->assertSame($id, $dsOrderStatusUpdated->getNbOrderId());
        $this->assertSame(ProcessingStatus::New, $dsOrderStatusUpdated->getNbOrderStatus());
    }
}
