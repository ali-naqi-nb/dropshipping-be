<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Bus\Query;

final class FirstMultipleHandledDummyQueryHandler
{
    public function __invoke(MultipleHandledDummyQuery $query): DummyQueryResponse
    {
        return new DummyQueryResponse();
    }
}
