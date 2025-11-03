<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Rpc\Server\CommandExecutor;

use App\Infrastructure\Messenger\SerializerInterface;
use App\Infrastructure\Rpc\Exception\InvalidParametersException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\Server\CommandExecutor\CommandsRegistry;
use App\Infrastructure\Rpc\Server\CommandExecutor\RpcCommandExecutor;
use App\Infrastructure\Rpc\Service\ClockInterface;
use App\Tests\Shared\Factory\RpcCommandFactory;
use App\Tests\Unit\Infrastructure\Rpc\Server\CommandExecutor\Resources\Controller;
use App\Tests\Unit\Infrastructure\Rpc\Server\CommandExecutor\Resources\Dto;
use App\Tests\Unit\UnitTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionMethod;
use ReflectionObject;

final class CommandExecutorTest extends UnitTestCase
{
    private SerializerInterface&MockObject $serializer;
    private ClockInterface&MockObject $clock;
    private RpcCommandExecutor $commandExecutor;

    public function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->serializer->method('deserialize')
            ->willReturnCallback(function ($data, $type) {
                $decoded = json_decode($data, true);

                if (is_scalar($decoded) || null === $decoded) {
                    return $decoded;
                }

                return new $type(...$decoded);
            });

        $this->clock = $this->createMock(ClockInterface::class);
        $this->clock->method('now')->willReturn(new DateTimeImmutable('@1641084245'));

        $controller = new Controller();
        $reflectionObject = new ReflectionObject($controller);
        $commandsMap = [];

        foreach ($reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $commandsMap[$method->getName()] = [$controller, $method->getName()];
        }

        $commandsRegistry = new CommandsRegistry($commandsMap);
        $this->commandExecutor = new RpcCommandExecutor($commandsRegistry, $this->serializer);
    }

    /**
     * @dataProvider provideExecuteData
     */
    public function testExecute(RpcCommand $command, mixed $expectedResult): void
    {
        $result = $this->commandExecutor->execute($command->getCommandId(), $command->getCommand(), $command->getArguments());

        $this->assertEquals($expectedResult, $result);
    }

    public function testExecuteMissingRequiredParameters(): void
    {
        $rpcCommand = RpcCommandFactory::getRpcCommand(
            command: 'multipleParameters',
            arguments: [
                'id' => 123,
            ],
        );

        $this->expectException(InvalidParametersException::class);
        $this->expectExceptionMessage('Missing required parameters: inputDto, mixed, union');

        $this->commandExecutor->execute($rpcCommand->getCommandId(), $rpcCommand->getCommand(), $rpcCommand->getArguments());
    }

    public function provideExecuteData(): array
    {
        return [
            'void' => [
                'command' => RpcCommandFactory::getRpcCommand(command: 'void'),
                'expectedResult' => null,
            ],
            'primitive' => [
                'command' => RpcCommandFactory::getRpcCommand(
                    command: 'primitive',
                    arguments: [3],
                ),
                'expectedResult' => 3,
            ],
            'object' => [
                'command' => RpcCommandFactory::getRpcCommand(
                    command: 'object',
                    arguments: [
                        [
                            'id' => 3,
                            'status' => 'test',
                        ],
                    ],
                ),
                'expectedResult' => new Dto(3, 'test'),
            ],
            'mixedArray' => [
                'command' => RpcCommandFactory::getRpcCommand(
                    command: 'mixed',
                    arguments: [
                        [
                            'id' => 3,
                            'status' => 'test',
                        ],
                    ],
                ),
                'expectedResult' => [
                    'id' => 3,
                    'status' => 'test',
                ],
            ],
            'mixedString' => [
                'command' => RpcCommandFactory::getRpcCommand(
                    command: 'mixed',
                    arguments: [
                        'string',
                    ],
                ),
                'expectedResult' => 'string',
            ],
            'default' => [
                'command' => RpcCommandFactory::getRpcCommand(
                    command: 'default',
                    arguments: []
                ),
                'expectedResult' => 3,
            ],
            'unionInt' => [
                'command' => RpcCommandFactory::getRpcCommand(
                    command: 'union',
                    arguments: [3],
                ),
                'expectedResult' => 3,
            ],
            'unionString' => [
                'command' => RpcCommandFactory::getRpcCommand(
                    command: 'union',
                    arguments: ['string'],
                ),
                'expectedResult' => 'string',
            ],
        ];
    }
}
