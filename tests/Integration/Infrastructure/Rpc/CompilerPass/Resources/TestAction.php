<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources;

use App\Infrastructure\Rpc\Attribute\Rpc;

final class TestAction
{
    #[Rpc]
    public function testAction(): void
    {
    }
}
