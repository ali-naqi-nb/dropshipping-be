<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Server\CommandExecutor;

use App\Infrastructure\Messenger\SerializerInterface;
use App\Infrastructure\Rpc\Exception\InvalidParametersException;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * This class executes RPC commands.
 *
 * It retrieves the service and method for a given command name, deserializes arguments based on parameter types,
 * and calls the method with those arguments. It handles errors during deserialization or execution and returns a
 * result object containing the execution status and response data.
 */
final class RpcCommandExecutor implements RpcCommandExecutorInterface
{
    public function __construct(
        private readonly CommandsRegistry $controllersRegistry,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @return \Exception
     *
     * @throws \ReflectionException
     */
    public function execute(string $id, string $command, array $arguments): mixed
    {
        $controller = $this->controllersRegistry->getController($command);

        $reflection = new ReflectionMethod($controller[0], $controller[1]);
        $deserializedArguments = [];
        $invalidArgumentTypes = [];
        $requiredParameters = [];

        foreach ($reflection->getParameters() as $index => $parameter) {
            if (!$parameter->isOptional()) {
                $requiredParameters[] = $parameter->getName();
            }

            if (!array_key_exists($parameter->getName(), $arguments) && !array_key_exists($index, $arguments)) {
                continue;
            }

            $argument = $arguments[$parameter->getName()] ?? $arguments[$index];

            if ($parameter->getType() instanceof ReflectionNamedType && 'mixed' !== $parameter->getType()->getName()) {
                try {
                    $parameterType = $parameter->getType();

                    $deserializedArguments[$parameter->name] = $this->serializer->deserialize(
                        json_encode($argument),
                        $parameterType->getName(),
                        'json'
                    );
                } catch (NotNormalizableValueException $e) {
                    $invalidArgumentTypes[$parameter->getName()] = $e->getMessage();
                }
            } else {
                $deserializedArguments[$parameter->name] = $argument;
            }
        }

        if (count($invalidArgumentTypes)) {
            throw new InvalidParametersException(implode(' ', array_map(fn (string $key, string $message) => "$key: $message", array_keys($invalidArgumentTypes), $invalidArgumentTypes)));
        }

        if ($reflection->getNumberOfRequiredParameters() !== count($deserializedArguments)) {
            $missingRequiredParameters = array_diff($requiredParameters, array_keys($deserializedArguments));

            throw new InvalidParametersException(sprintf('Missing required parameters: %s', implode(', ', $missingRequiredParameters)));
        }

        return $controller[0]->{$controller[1]}(...$deserializedArguments);
    }
}
