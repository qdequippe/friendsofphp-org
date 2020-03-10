<?php

declare(strict_types=1);

namespace Fop\StaticSiteDumper\Command;

use Nette\Utils\FileSystem;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class DumpStaticSiteCommand extends Command
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(RouterInterface $router, ContainerInterface $container, SymfonyStyle $symfonyStyle)
    {
        $this->router = $router;
        $this->container = $container;
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle->section(sprintf(
            'Dumping static website for %d routes', count($this->router->getRouteCollection()))
        );

        foreach ($this->router->getRouteCollection() as $route) {
            $controllerClass = $route->getDefault('_controller');

            $this->symfonyStyle->note(sprintf('Dumping static content for "%s" class to "%s" path', $controllerClass, $route->getPath()));

            $controller = $this->container->get($controllerClass);
            dump((string) $controller());

            FileSystem::write( getcwd() . '/output/' . );


            die;
        }

        return ShellCode::SUCCESS;
    }
}
