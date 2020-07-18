<?php declare(strict_types=1);

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->load('Fop\MeetupCom\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/ValueObject/*']);

    $services->set(Client::class);

    $services->set(StringFormatConverter::class);
};
