<?php declare(strict_types=1);

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use Symplify\PackageBuilder\Http\BetterGuzzleClient;
use Symplify\PackageBuilder\Yaml\ParameterMergingYamlLoader;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/parameters.php');

    $containerConfigurator->import(__DIR__ . '/../vendor/symplify/symfony-static-dumper/config/config.php');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Fop\Core\\', __DIR__ . '/../src')
        ->exclude(
            [
                __DIR__ . '/../src/Entity/*',
                __DIR__ . '/../src/HttpKernel/*',
                __DIR__ . '/../src/Contract/*',
                __DIR__ . '/../src/ValueObject/*',
            ]
        );

    $services->set(ParameterMergingYamlLoader::class);

    $services->set(SymfonyStyleFactory::class);

    $services->set(Client::class);

    $services->set(BetterGuzzleClient::class);
};
