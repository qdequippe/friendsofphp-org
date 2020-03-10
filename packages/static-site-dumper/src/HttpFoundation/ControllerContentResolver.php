<?php

declare(strict_types=1);

namespace Fop\StaticSiteDumper\HttpFoundation;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

final class ControllerContentResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolveFromRoute(Route $route): ?string
    {
        $controllerClass = $route->getDefault('_controller');
        if (! class_exists($controllerClass)) {
            return null;
        }

        $controller = $this->container->get($controllerClass);
        /** @var Response $response */
        $response = $controller();

        return (string) $response->getContent();
    }
}
