<?php

namespace App;

use App\Infrastructure\Rpc\CompilerPass\RpcCommandCompilerPass;
use App\Infrastructure\Rpc\CompilerPass\RpcCommandDetector;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * @codeCoverageIgnore
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RpcCommandCompilerPass(new RpcCommandDetector()));
    }
}
