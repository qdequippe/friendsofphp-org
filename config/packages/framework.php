<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\Amnesia\ValueObject\Symfony\Extension\FrameworkExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(FrameworkExtension::NAME, [
        FrameworkExtension::SECRET => '%env(APP_SECRET)%',
    ]);
};
