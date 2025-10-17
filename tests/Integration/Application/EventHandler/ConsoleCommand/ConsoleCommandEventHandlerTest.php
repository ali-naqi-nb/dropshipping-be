<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\EventHandler\ConsoleCommand;

use App\Application\EventHandler\ConsoleCommand\ConsoleCommandEventHandler;
use App\Domain\Model\ConsoleCommand\ConsoleCommandEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ConsoleCommandEventHandlerTest extends TestCase
{
    private ConsoleCommandEventHandler $handler;
    private MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new ConsoleCommandEventHandler($this->logger);
    }

    public function testCommandExecutionErrorLogsError(): void
    {
        $event = $this->createMock(ConsoleCommandEvent::class);
        $event->method('getCommand')->willReturn('app:invalid:command');
        $event->method('getArguments')->willReturn(['email' => 'testmail@mail.com', 'password' => 'password']);
        $event->method('getService')->willReturn('dropshipping-manager');

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error executing console command with output:'));

        $this->handler->__invoke($event);
    }

    public function testCommandNotForServiceDoesNothing(): void
    {
        $event = $this->createMock(ConsoleCommandEvent::class);
        $event->method('getCommand')->willReturn('app:import-zip-codes');
        $event->method('getArguments')->willReturn([]);
        $event->method('getService')->willReturn('iam');

        $this->logger->expects($this->exactly(0))
            ->method('info');

        $this->handler->__invoke($event);
    }
}
