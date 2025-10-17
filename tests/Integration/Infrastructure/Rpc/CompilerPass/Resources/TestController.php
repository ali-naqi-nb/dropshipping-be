<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources;

use App\Infrastructure\Rpc\Attribute\Rpc;

final class TestController
{
    #[Rpc]
    public function controllerAction(): void
    {
    }

    #[Rpc(service: 'test_service', command: 'test_controller_action')]
    public function namedControllerAction(): void
    {
    }

    #[Rpc(command: 'test_controller_action')]
    public function namedControllerDefaultServiceAction(): void
    {
    }

    public function nonRpcAction(): void
    {
    }
}
