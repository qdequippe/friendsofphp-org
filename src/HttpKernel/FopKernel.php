<?php

declare(strict_types=1);

namespace Fop\HttpKernel;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\SymfonyStaticDumper\ValueObject\SymfonyStaticDumperConfig;
use Symplify\SymplifyKernel\ValueObject\SymplifyKernelConfig;

final class FopKernel extends Kernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/config.php');
        $loader->load(SymfonyStaticDumperConfig::FILE_PATH);

        $loader->load(SymplifyKernelConfig::FILE_PATH);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/../../config/routes.php');
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }
}
