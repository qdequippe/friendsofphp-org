<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Rector\SymfonyCodeQuality\Rector\Attribute\ExtractAttributeRouteNameConstantsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ExtractAttributeRouteNameConstantsRector::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
       __DIR__ . '/src',
       __DIR__ . '/packages',
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

//    $parameters->set(Option::SETS, [
//       SetList::PHP_80,
//       SetList::PHP_74,
//       SetList::PHP_73,
//       SetList::PHP_72,
//       SetList::PHP_71,
//       SetList::DEAD_CODE,
//       SetList::CODE_QUALITY,
//       SetList::PRIVATIZATION,
//       SetList::NAMING,
//       SetList::TYPE_DECLARATION,
//    ]);
};
