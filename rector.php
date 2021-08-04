<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::PHP_80);
    $containerConfigurator->import(SetList::PHP_74);
    $containerConfigurator->import(SetList::PHP_73);
    $containerConfigurator->import(SetList::PHP_72);
    $containerConfigurator->import(SetList::PHP_71);
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::PRIVATIZATION);
    $containerConfigurator->import(SetList::NAMING);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);

    $services = $containerConfigurator->services();

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/packages', __DIR__ . '/tests']);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services->set(ChangeReadOnlyPropertyWithDefaultValueToConstantRector::class);

    $parameters->set(Option::SKIP, [
        // buggy because of MicroKernel trait fuckups
        PrivatizeFinalClassMethodRector::class => [__DIR__ . '/src/HttpKernel/FopKernel.php'],

        RemoveUnusedPromotedPropertyRector::class => [
            __DIR__ . '/packages/meetup/src/Repository/GroupRepository.php',
        ],

        RenamePropertyToMatchTypeRector::class => [
            // un-wanted renamed, compatize later
            __DIR__ . '/packages/meetup/src/ValueObject/Meetup.php',
        ],
    ]);
};
