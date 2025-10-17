<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\CompilerPass;

use App\Infrastructure\Rpc\Attribute\Rpc;
use App\Infrastructure\Rpc\Server\CommandExecutor\CommandsRegistry;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This class automatically finds services meant for RPC commands and registers them with a central registry during startup.
 * It scans services for the RPC attributes to build unique command names and link them to the appropriate methods.
 *
 * @codeCoverageIgnore
 */
final class RpcCommandCompilerPass implements CompilerPassInterface
{
    public function __construct(private readonly RpcCommandDetector $commandDetector)
    {
    }

    public function process(ContainerBuilder $container): void
    {
        $registry = $container->findDefinition(CommandsRegistry::class);
        /** @var string $defaultService */
        $defaultService = $container->getParameter('app.service_name') ?? '';
        $addedCommands = [];

        foreach ($container->getDefinitions() as $id => $definition) {
            /** @var class-string $class */
            $class = $definition->getClass();
            if (null == $class) {
                continue;
            }
            try {
                new \ReflectionClass($class);
                /* @phpstan-ignore-next-line */
            } catch (\Throwable) {
                continue;
            }

            $commands = $this->commandDetector->detectCommandsInClass($class, $defaultService);
            foreach ($commands as $commandDefinition) {
                [$command, $method] = $commandDefinition;

                if (array_key_exists(strtolower($command), $addedCommands)) {
                    throw new InvalidArgumentException(sprintf('Unable to register command "%s" by "%s". Command with the same name is already registered by "%s"', $command, $id, $addedCommands[strtolower($command)]));
                }

                $addedCommands[strtolower($command)] = $id;

                $container->getDefinition($id)->setPublic(true);

                $registry->addMethodCall('addController', [$command, $container->getDefinition($id), $method]);
            }
        }
    }
}
