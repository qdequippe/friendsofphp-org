<?php

declare(strict_types=1);

use Fop\Core\ValueObject\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // how many days into the future the meetups should be imported
    $parameters->set(Option::MAX_FORECAST_DAYS, 30);
};
