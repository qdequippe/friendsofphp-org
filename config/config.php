<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/parameters.php');
    $containerConfigurator->import(__DIR__ . '/packages/*');
    $containerConfigurator->import(__DIR__ . '/../packages/*/config/*');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Fop\Core\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/HttpKernel']);

    $services->set(ParameterProvider::class);
    $services->set(PrivatesAccessor::class);
    $services->set(Client::class);
};
