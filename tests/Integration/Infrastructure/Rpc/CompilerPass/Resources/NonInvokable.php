<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources;

final class NonInvokable
{
    public function test(): void
    {
    }
}
