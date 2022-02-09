<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Jajo\JSONDB;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../packages/*/config/*');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Fop\Core\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/HttpKernel', __DIR__ . '/../src/ValueObject']);

    $services->set(Client::class);

    $services->set(JSONDB::class)
        ->arg('$dir', __DIR__ . '/../json-database');

    $containerConfigurator->extension('framework', [
        'secret' => '%env(APP_SECRET)%',
    ]);

    $containerConfigurator->extension('twig', [
        'debug' => true,
        'strict_variables' => true,
        'globals' => [
            'map_id' => 'mapid',
        ],
    ]);
};
