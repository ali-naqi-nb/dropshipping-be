<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Bus\Query;

final class SecondMultipleHandledDummyQueryHandler
{
    public function __invoke(MultipleHandledDummyQuery $command): DummyQueryResponse
    {
        return new DummyQueryResponse();
    }
}
