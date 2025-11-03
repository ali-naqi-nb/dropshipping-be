<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Domain\Model\Order\ProcessingStatus;
use App\Tests\Unit\UnitTestCase;

/**
 * This test case is created in order to prevent updating by accident order processing status enum
 * which could affect business logic.
 */
final class ProcessingStatusTest extends UnitTestCase
{
    public function testEnumCount(): void
    {
        $this->assertCount(5, ProcessingStatus::cases());
    }

    /** @dataProvider provideStatus */
    public function testNameAndValueExist(ProcessingStatus $status, string $value): void
    {
        $this->assertSame($value, $status->value);
    }

    /** @dataProvider provideAeMappings */
    public function testGetAeMappingOrderStatus(string $aeOrderStatus, ProcessingStatus $status): void
    {
        $this->assertSame(ProcessingStatus::getAeMappingOrderStatus($aeOrderStatus), $status);
    }

    public function provideStatus(): array
    {
        return [
            'new' => [ProcessingStatus::New, 'new'],
            'processing' => [ProcessingStatus::Processing, 'processing'],
            'shipped' => [ProcessingStatus::Shipped, 'shipped'],
            'delivered' => [ProcessingStatus::Delivered, 'delivered'],
            'canceled' => [ProcessingStatus::Canceled, 'canceled'],
        ];
    }

    public function provideAeMappings(): array
    {
        return [
            'paymentFailedEvent' => ['paymentFailedEvent', ProcessingStatus::New],
            'OrderCreated' => ['OrderCreated', ProcessingStatus::New],
            'OrderClosed' => ['OrderClosed', ProcessingStatus::Canceled],
            'PaymentAuthorized' => ['PaymentAuthorized', ProcessingStatus::Processing],
            'OrderShipped' => ['OrderShipped', ProcessingStatus::Shipped],
            'OrderConfirmed' => ['OrderConfirmed', ProcessingStatus::Delivered],
        ];
    }
}
