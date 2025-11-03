<?php

declare(strict_types=1);

namespace App\Tests\Shared\Trait;

use App\Infrastructure\Delivery\Rpc\V1\Order\GetOrdersBySourceAction;
use App\Infrastructure\Delivery\Rpc\V1\Product\DsAttributesImportedAction;
use App\Infrastructure\Delivery\Rpc\V1\Product\DsProductGroupImportedAction;
use App\Infrastructure\Delivery\Rpc\V1\Product\DsProductImagesImportedAction;
use App\Infrastructure\Delivery\Rpc\V1\Product\DsProductImagesUpdatedAction;
use App\Infrastructure\Delivery\Rpc\V1\Product\DsProductTypeImportedAction;
use App\Infrastructure\Messenger\SerializerInterface;
use App\Infrastructure\Rpc\Exception\ClientException;
use App\Infrastructure\Rpc\Exception\InvalidParametersException;
use App\Infrastructure\Rpc\Exception\InvalidRequestException;
use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Rpc\RpcResult;
use App\Infrastructure\Rpc\RpcResultStatus;
use App\Tests\Functional\Infrastructure\Delivery\Rpc\V1\Test\InvalidParamsAction;
use App\Tests\Functional\Infrastructure\Delivery\Rpc\V1\Test\InvalidRequestAction;
use App\Tests\Shared\Factory\RpcResultFactory;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

trait RpcTestBootTrait
{
    protected array $commands = [
        'testInvalidParams' => InvalidParamsAction::class,
        'testInvalidRequest' => InvalidRequestAction::class,
        'dsAttributesImported' => DsAttributesImportedAction::class,
        'dsProductGroupImported' => DsProductGroupImportedAction::class,
        'dsProductTypeImported' => DsProductTypeImportedAction::class,
        'dsProductImagesImported' => DsProductImagesImportedAction::class,
        'dsProductImagesUpdated' => DsProductImagesUpdatedAction::class,
        'getOrdersBySource' => GetOrdersBySourceAction::class,
    ];

    /**
     * @throws ReflectionException
     */
    protected function emulateRpcCommand(string $service, string $command, array $arguments = []): void
    {
        if (!array_key_exists($command, $this->commands)) {
            return;
        }

        /** @var SerializerInterface $rpcSerializer */
        $rpcSerializer = self::getContainer()->get(SerializerInterface::class);

        $class = $this->commands[$command];
        $controller = self::getContainer()->get($class);

        $reflection = new ReflectionMethod($class, '__invoke');

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

                    if ('object' === gettype($argument) && $parameterType->getName() === get_class($argument)) {
                        $deserializedArguments[$parameter->name] = $argument;
                    } else {
                        $deserializedArguments[$parameter->name] = $rpcSerializer->deserialize(
                            json_encode($argument),
                            $parameterType->getName(),
                            'json'
                        );
                    }
                } catch (NotNormalizableValueException|MissingConstructorArgumentsException $e) {
                    $invalidArgumentTypes[$parameter->getName()] = $e->getMessage();
                }
            } else {
                $deserializedArguments[$parameter->name] = $argument;
            }
        }

        try {
            if (count($invalidArgumentTypes)) {
                throw new InvalidParametersException(implode(' ', array_map(fn (string $key, string $message) => "$key: $message", array_keys($invalidArgumentTypes), $invalidArgumentTypes)));
            }

            if ($reflection->getNumberOfRequiredParameters() !== count($deserializedArguments)) {
                $missingRequiredParameters = array_diff($requiredParameters, array_keys($deserializedArguments));

                throw new InvalidParametersException(sprintf('Missing required parameters: %s', implode(', ', $missingRequiredParameters)));
            }

            /** @phpstan-ignore-next-line */
            $result = $controller->__invoke(...$deserializedArguments);

            $rpcResult = RpcResultFactory::getRpcCommandResult(status: RpcResultStatus::SUCCESS, result: $result);
        } catch (ClientException $exception) {
            $data = null;

            if ($exception instanceof InvalidRequestException) {
                $data = $exception->getData();
            }

            $rpcResult = RpcResultFactory::getRpcCommandResult(status: RpcResultStatus::ERROR, result: [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'data' => $data,
            ]);
        }

        $serialized = $rpcSerializer->serialize($rpcResult, 'json');
        $deserialized = $rpcSerializer->deserialize($serialized, RpcResult::class, 'json');

        $this->mockRpcResponse(
            function (RpcCommand $rpcCommand) use ($service, $command) {
                return "$service.$command" === $rpcCommand->getCommand();
            },
            $deserialized
        );
    }
}
