<?php declare(strict_types=1);

namespace Fop\DependencyInjection;

use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutoBindParametersCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutoReturnFactoryCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\ConfigurableCollectorCompilerPass;
use Symplify\PackageBuilder\HttpKernel\SimpleKernelTrait;
use Symplify\PackageBuilder\Yaml\FileLoader\ParameterMergingYamlFileLoader;

final class FopKernel extends Kernel
{
    use SimpleKernelTrait;

    /**
     * @var string|null
     */
    private $config;

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../packages/*/config/config.yaml', 'glob');
        $loader->load(__DIR__ . '/../../config/config.yaml');

        if ($this->config) {
            $loader->load($this->config);
        }
    }

    public function bootWithConfig(string $config): void
    {
        $this->config = $config;
        $this->boot();
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new AutoReturnFactoryCompilerPass());
        $containerBuilder->addCompilerPass(new ConfigurableCollectorCompilerPass());
        $containerBuilder->addCompilerPass(new AutoBindParametersCompilerPass());
    }

    /**
     * @param ContainerInterface|ContainerBuilder $container
     */
    protected function getContainerLoader(ContainerInterface $container): DelegatingLoader
    {
        $kernelFileLocator = new FileLocator($this);
        $loaderResolver = new LoaderResolver([
            new GlobFileLoader($kernelFileLocator),
            new ParameterMergingYamlFileLoader($container, $kernelFileLocator),
        ]);

        return new DelegatingLoader($loaderResolver);
    }
}
