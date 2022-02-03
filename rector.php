<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector;
use Rector\Core\Configuration\Option;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(LevelSetList::UP_TO_PHP_81);
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::PRIVATIZATION);
    $containerConfigurator->import(SetList::NAMING);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);

    $services = $containerConfigurator->services();

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/packages', __DIR__ . '/tests']);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::PARALLEL, true);

    $parameters->set(Option::SKIP, [
        // buggy because of MicroKernel trait magic
        PrivatizeFinalClassMethodRector::class => [__DIR__ . '/src/HttpKernel/FopKernel.php'],
        DateTimeToDateTimeInterfaceRector::class => [
            __DIR__ . '/packages/meetup-com/src/Meetup/MeetupComMeetupFactory.php',
        ],
    ]);
};
