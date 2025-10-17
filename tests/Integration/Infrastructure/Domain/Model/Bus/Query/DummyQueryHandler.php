<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Bus\Query;

final class DummyQueryHandler
{
    public function __invoke(DummyQuery $query): DummyQueryResponse
    {
        return new DummyQueryResponse();
    }
}
