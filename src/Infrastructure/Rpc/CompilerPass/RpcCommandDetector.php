<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\CompilerPass;

use App\Infrastructure\Rpc\Attribute\Rpc;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

final class RpcCommandDetector
{
    /**
     * @param class-string $class
     *
     * @throws \ReflectionException
     */
    public function detectCommandsInClass(string $class, string $defaultService): array
    {
        $rpcCommands = [];
        $reflection = new ReflectionClass($class);

        $attributes = $reflection->getAttributes(Rpc::class);

        if (count($attributes)) {
            if (!$reflection->hasMethod('__invoke') || !$reflection->getMethod('__invoke')->isPublic()) {
                return throw new InvalidArgumentException(sprintf('Class "%s" has Rpc attribute but is not invokable', $class));
            }

            /** @var Rpc $rpcAttribute */
            $rpcAttribute = $attributes[0]->newInstance();
            $className = $reflection->getShortName();
            $className = str_replace('Controller', '', $className);
            $className = str_replace('Action', '', $className);
            $service = $rpcAttribute->getService() ?? $defaultService;
            $command = $rpcAttribute->getCommand() ?? $className;

            $rpcCommands[] = [
                strtolower("$service.$command"),
                '__invoke',
            ];
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Rpc::class);

            if (count($attributes)) {
                /** @var Rpc $rpcAttribute */
                $rpcAttribute = $attributes[0]->newInstance();
                $service = $rpcAttribute->getService() ?? $defaultService;
                $command = $rpcAttribute->getCommand() ?? $method->getName();

                $rpcCommands[] = [
                    strtolower("$service.$command"),
                    $method->getName(),
                ];
            }
        }

        return $rpcCommands;
    }
}
