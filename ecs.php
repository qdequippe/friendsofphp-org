<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(LineLengthFixer::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PARALLEL, true);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/bin',
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/packages',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]);

    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::SYMPLIFY);
    $containerConfigurator->import(SetList::PSR_12);

    $parameters->set(Option::SKIP, [__DIR__ . '/config/bundles.php']);
};
