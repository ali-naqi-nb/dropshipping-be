<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Rpc\CompilerPass;

use App\Infrastructure\Rpc\CompilerPass\RpcCommandDetector;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\InvokableAction;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\NamedTestAction;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\NonInvokable;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\TestAction;
use App\Tests\Integration\Infrastructure\Rpc\CompilerPass\Resources\TestController;
use App\Tests\Unit\UnitTestCase;

final class RpcCommandDetectorTest extends UnitTestCase
{
    /**
     * @dataProvider provideDetectCommandsInClassData
     *
     * @param class-string $class
     */
    public function testDetectCommandsInClass(string $class, string $defaultService, array $expectedCommands): void
    {
        $commandDetector = new RpcCommandDetector();

        $detectedCommands = $commandDetector->detectCommandsInClass($class, $defaultService);

        $this->assertSame($expectedCommands, $detectedCommands);
    }

    public function provideDetectCommandsInClassData(): array
    {
        return [
            'invokableClass' => [
                'class' => InvokableAction::class,
                'defaultService' => 'default_service',
                'expectedCommands' => [
                    [
                        'default_service.invokable',
                        '__invoke',
                    ],
                ],
            ],
            'namedTestAction' => [
                'class' => NamedTestAction::class,
                'defaultService' => 'default_service',
                'expectedCommands' => [
                    [
                        'test_service.named_test_action',
                        'testAction',
                    ],
                ],
            ],
            'testAction' => [
                'class' => TestAction::class,
                'defaultService' => 'default_service',
                'expectedCommands' => [
                    [
                        'default_service.testaction',
                        'testAction',
                    ],
                ],
            ],
            'nonInvokable' => [
                'class' => NonInvokable::class,
                'defaultService' => 'default_service',
                'expectedCommands' => [],
            ],
            'testController' => [
                'class' => TestController::class,
                'defaultService' => 'default_service',
                'expectedCommands' => [
                    [
                        'default_service.controlleraction',
                        'controllerAction',
                    ],
                    [
                        'test_service.test_controller_action',
                        'namedControllerAction',
                    ],
                    [
                        'default_service.test_controller_action',
                        'namedControllerDefaultServiceAction',
                    ],
                ],
            ],
        ];
    }
}
