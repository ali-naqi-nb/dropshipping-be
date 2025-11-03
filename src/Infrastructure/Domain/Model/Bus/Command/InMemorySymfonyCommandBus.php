<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Bus\Command;

use App\Domain\Model\Bus\Command\CommandBusInterface;
use App\Domain\Model\Bus\Command\CommandInterface;
use App\Domain\Model\Bus\Command\CommandResponseInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class InMemorySymfonyCommandBus implements CommandBusInterface
{
    use HandleTrait;

    /**
     * @param MessageBusInterface $commandBus Name is important because we want to use command bus
     */
    public function __construct(MessageBusInterface $commandBus)
    {
        $this->messageBus = $commandBus;
    }

    public function dispatch(CommandInterface $command): ?CommandResponseInterface
    {
        /** @var ?CommandResponseInterface $response */
        $response = $this->handle($command);

        return $response;
    }
}
