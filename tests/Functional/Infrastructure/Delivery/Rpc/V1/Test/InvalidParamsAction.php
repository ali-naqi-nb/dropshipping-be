<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Rpc\V1\Test;

use App\Infrastructure\Rpc\Attribute\Rpc;

#[Rpc(command: 'testInvalidParams')]
final class InvalidParamsAction
{
    public function __invoke(string $param): void
    {
    }
}
