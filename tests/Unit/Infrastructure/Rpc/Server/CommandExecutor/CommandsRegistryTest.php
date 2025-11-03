<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Server\CommandExecutor;

use App\Infrastructure\Rpc\Exception\CommandNotFoundException;
use App\Infrastructure\Rpc\Server\CommandExecutor\CommandsRegistry;
use App\Tests\Unit\UnitTestCase;
use stdClass;

final class CommandsRegistryTest extends UnitTestCase
{
    public function testAddController(): void
    {
        $registry = new CommandsRegistry();
        $controllerObject = new stdClass();

        $registry->addController('command', $controllerObject, 'method');

        $controller = $registry->getController('command');

        $this->assertSame($controllerObject, $controller[0]);
        $this->assertSame('method', $controller[1]);
    }

    public function testGetController(): void
    {
        $controllerObject1 = new stdClass();
        $controllerObject2 = new stdClass();

        $registry = new CommandsRegistry([
            'command1' => [$controllerObject1, 'method1'],
        ]);

        $registry->addController('command2', $controllerObject2, 'method2');

        $controller1 = $registry->getController('command1');
        $controller2 = $registry->getController('command2');

        $this->assertSame($controllerObject1, $controller1[0]);
        $this->assertSame('method1', $controller1[1]);

        $this->assertSame($controllerObject2, $controller2[0]);
        $this->assertSame('method2', $controller2[1]);
    }

    public function testGetControllerThrowsExceptionIfCommandNotFound(): void
    {
        $this->expectException(CommandNotFoundException::class);

        $registry = new CommandsRegistry();
        $registry->getController('nonExistentCommand');
    }
}
