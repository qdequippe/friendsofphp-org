<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector;
use Rector\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/packages', __DIR__ . '/tests']);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services->set(ChangeReadOnlyPropertyWithDefaultValueToConstantRector::class);

    $parameters->set(Option::SETS, [
        SetList::PHP_80,
        SetList::PHP_74,
        SetList::PHP_73,
        SetList::PHP_72,
        SetList::PHP_71,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
    ]);

    $parameters->set(Option::SKIP, [
        // buggy because of MicroKernel trait fuckups
        PrivatizeLocalOnlyMethodRector:: class => [__DIR__ . '/src/HttpKernel/FopKernel.php'],
        PrivatizeFinalClassMethodRector::class => [__DIR__ . '/src/HttpKernel/FopKernel.php'],

        RenamePropertyToMatchTypeRector::class => [
            // un-wanted renamed, compatize later
            __DIR__ . '/packages/meetup/src/ValueObject/Meetup.php',
        ],
    ]);
};
