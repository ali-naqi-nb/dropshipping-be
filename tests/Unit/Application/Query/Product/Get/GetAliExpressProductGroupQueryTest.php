<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query\Product\Get;

use App\Application\Query\Product\Get\GetAliExpressProductGroupQuery;
use App\Tests\Unit\UnitTestCase;

final class GetAliExpressProductGroupQueryTest extends UnitTestCase
{
    public function testGetter(): void
    {
        $id = '550e8400-e29b-41d4-a716-446655440000';
        $query = new GetAliExpressProductGroupQuery($id);

        $this->assertSame($id, $query->getId());
    }
}
