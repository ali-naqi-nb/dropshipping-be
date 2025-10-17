<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\CompilerPass;

use App\Infrastructure\Rpc\Server\CommandExecutor\CommandsRegistry;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\InvokableAction;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\NamedTestAction;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\TestAction;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\TestController;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class RpcCommandCompilerPassTest extends IntegrationTestCase
{
    public function testRegisteredCommands(): void
    {
        /** @var ContainerInterface $container */
        $container = self::getContainer();
        /** @var CommandsRegistry $commandsRegistry */
        $commandsRegistry = $container->get(CommandsRegistry::class);
        /** @var string $defaultServiceName */
        $defaultServiceName = $container->getParameter('app.service_name') ?? '';

        $expectedControllers = [
            "$defaultServiceName.invokable" => [
                $container->get(InvokableAction::class),
                '__invoke',
            ],
            'test_service.named_test_action' => [
                $container->get(NamedTestAction::class),
                'testAction',
            ],
            "$defaultServiceName.testAction" => [
                $container->get(TestAction::class),
                'testAction',
            ],
            "$defaultServiceName.controllerAction" => [
                $container->get(TestController::class),
                'controllerAction',
            ],
            'test_service.test_controller_action' => [
                $container->get(TestController::class),
                'namedControllerAction',
            ],
            "$defaultServiceName.test_controller_action" => [
                $container->get(TestController::class),
                'namedControllerDefaultServiceAction',
            ],
        ];

        foreach ($expectedControllers as $command => $expectedController) {
            $controller = $commandsRegistry->getController($command);
            $this->assertEquals($expectedController, $controller, "$command not found");
        }
    }
}
