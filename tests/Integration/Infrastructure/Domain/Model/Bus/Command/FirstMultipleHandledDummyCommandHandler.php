<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Bus\Command;

final class FirstMultipleHandledDummyCommandHandler
{
    public function __invoke(MultipleHandledDummyCommand $command): DummyCommandResponse
    {
        return new DummyCommandResponse();
    }
}
