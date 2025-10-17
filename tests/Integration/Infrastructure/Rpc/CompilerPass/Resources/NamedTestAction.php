<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources;

use App\Infrastructure\Rpc\Attribute\Rpc;

final class NamedTestAction
{
    #[Rpc(service: 'test_service', command: 'named_test_action')]
    public function testAction(): void
    {
    }
}
