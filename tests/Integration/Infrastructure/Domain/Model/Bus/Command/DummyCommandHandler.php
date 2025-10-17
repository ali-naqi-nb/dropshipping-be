<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Bus\Command;

final class DummyCommandHandler
{
    public function __invoke(DummyCommand $command): DummyCommandResponse
    {
        return new DummyCommandResponse();
    }
}
