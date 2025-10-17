<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Rpc\V1\Test;

use App\Infrastructure\Rpc\Attribute\Rpc;
use App\Infrastructure\Rpc\Exception\InvalidRequestException;

#[Rpc(command: 'testInvalidRequest')]
final class InvalidRequestAction
{
    public function __invoke(): void
    {
        throw new InvalidRequestException('Invalid request');
    }
}
