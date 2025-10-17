<?php

declare(strict_types=1);

namespace App\Domain\Model\Bus\Command;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): ?CommandResponseInterface;
}
