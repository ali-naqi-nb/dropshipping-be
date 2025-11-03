<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\ConsoleCommand;

use App\Domain\Model\Bus\Event\DomainEventInterface;
use App\Domain\Model\ConsoleCommand\ConsoleCommandEvent;
use App\Tests\Unit\UnitTestCase;

final class ConsoleCommandEventTest extends UnitTestCase
{
    public function testCreateEventWithRequiredFields(): void
    {
        $command = 'app:import-zip-codes';
        $service = 'products';
        $arguments = [];

        $event = new ConsoleCommandEvent($command, $service, $arguments);

        $this->assertInstanceOf(DomainEventInterface::class, $event);
        $this->assertEquals($command, $event->getCommand());
        $this->assertEquals($service, $event->getService());
        $this->assertEquals($arguments, $event->getArguments());
    }

    public function testCreateEventWithArguments(): void
    {
        $command = 'app:import-zip-codes';
        $service = 'products';
        $arguments = [];

        $event = new ConsoleCommandEvent($command, $service, $arguments);

        $this->assertEquals($command, $event->getCommand());
        $this->assertEquals($service, $event->getService());
        $this->assertEquals($arguments, $event->getArguments());
    }

    public function testEventImplementsDomainEventInterface(): void
    {
        $event = new ConsoleCommandEvent('test:command', 'test_service', []);

        $this->assertInstanceOf(DomainEventInterface::class, $event);
    }
}
