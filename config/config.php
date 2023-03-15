<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Jajo\JSONDB;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Fop\\', __DIR__ . '/../src')
        ->exclude([
            __DIR__ . '/../src/HttpKernel',
            __DIR__ . '/../src/ValueObject',
            __DIR__ . '/../src/Meetup/ValueObject',
            __DIR__ . '/../src/MeetupCom/ValueObject',
        ]);

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

    $services->set(Client::class);
    $services->set(StringFormatConverter::class);

    $services->set(\Goutte\Client::class);
};
