<?php

declare(strict_types=1);

namespace Fop\StaticSiteDumper\HttpFoundation;

use Nette\Utils\Strings;
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
        $controller = $route->getDefault('_controller');

        if (Strings::contains($controller, '::')) {
            [$controllerClass, $method] = Strings::split($controller, '#::#');
        } else {
            $controllerClass = $controller;
            $method = '__invoke';
        }

        if (! class_exists($controllerClass)) {
            return null;
        }

        $controller = $this->container->get($controllerClass);

        /** @var Response $response */
        $response = call_user_func([$controller, $method]);

        return (string) $response->getContent();
    }
}
