<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Bus\Command;

use App\Domain\Model\Bus\Command\CommandBusInterface;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;

final class InMemorySymfonyCommandBusTest extends IntegrationTestCase
{
    private CommandBusInterface $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CommandBusInterface $commandBus */
        $commandBus = self::getContainer()->get(CommandBusInterface::class);
        $this->commandBus = $commandBus;
    }

    public function testCommandWithOnlyOneHandlerReturnsSuccess(): void
    {
        $this->assertInstanceOf(DummyCommandResponse::class, $this->commandBus->dispatch(new DummyCommand()));
    }

    public function testWithCommandWithoutHandlerThrowsException(): void
    {
        $this->expectException(NoHandlerForMessageException::class);
        $this->expectExceptionMessage('No handler for message');

        $this->commandBus->dispatch(new UnhandledDummyCommand());
    }

    public function testWithCommandWithMoreThanOneHandlerThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"App\Tests\Integration\Infrastructure\Domain\Model\Bus\Command\MultipleHandledDummyCommand" was handled multiple times.');

        $this->commandBus->dispatch(new MultipleHandledDummyCommand());
    }
}
