<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources;

use App\Infrastructure\Rpc\Attribute\Rpc;

#[Rpc]
final class InvokableAction
{
    public function __invoke(): void
    {
    }

    public function nonRpcAction(): void
    {
    }
}
