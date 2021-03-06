<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\Amnesia\ValueObject\Symfony\Extension\TwigExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(TwigExtension::NAME, [
        TwigExtension::DEBUG => '%kernel.debug%',
        TwigExtension::STRICT_VARIABLES => '%kernel.debug%',
        TwigExtension::GLOBALS => [
            'max_forecast_days' => '%max_forecast_days%',
            'map_id' => 'mapid',
        ],
    ]);
};
