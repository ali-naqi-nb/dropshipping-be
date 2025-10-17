<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command\Order;

use App\Application\Command\Order\AliexpressSyncOrderStatus\AliexpressSyncOrdersStatusCommand;
use App\Tests\Unit\UnitTestCase;

final class AliexpressSyncOrdersStatusCommandTest extends UnitTestCase
{
    public function testGettersWithSellerIdInData(): void
    {
        $data = [
            'buyerId' => 725164237,
            'orderId' => 3043908619534237,
            'orderStatus' => 'OrderCreated',
            'sellerId' => '725164237',
        ];

        $messageType = 53;
        $sellerId = null;
        $site = 'ae_global';
        $timestamp = 1730455911;

        $command = new AliexpressSyncOrdersStatusCommand(
            data: $data,
            message_type: $messageType,
            seller_id: $sellerId,
            site: $site,
            timestamp: $timestamp
        );

        $this->assertSame($data, $command->getData());
        $this->assertSame($messageType, $command->getMessageType());
        $this->assertSame($data['sellerId'], $command->getSellerId());
        $this->assertSame($site, $command->getSite());
        $this->assertSame($timestamp, $command->getTimestamp());
    }

    public function testGettersWithoutSellerIdInData(): void
    {
        $data = [
            'buyerId' => 725164237,
            'orderId' => 3043908619534237,
            'orderStatus' => 'OrderCreated',
        ];

        $messageType = 53;
        $sellerId = '725164237';
        $site = 'ae_global';
        $timestamp = 1730455911;

        $command = new AliexpressSyncOrdersStatusCommand(
            data: $data,
            message_type: $messageType,
            seller_id: $sellerId,
            site: $site,
            timestamp: $timestamp
        );

        $this->assertSame($data, $command->getData());
        $this->assertSame($messageType, $command->getMessageType());
        $this->assertSame($sellerId, $command->getSellerId());
        $this->assertSame($site, $command->getSite());
        $this->assertSame($timestamp, $command->getTimestamp());
    }
}
