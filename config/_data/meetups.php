<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('meetups', [[
        'name' => 'Monthly Meeting',
        'user_group_name' => 'AmsterdamPHP',
        'start_date_time' => '2021-08-19 19:00',
        'city' => 'Amsterdam',
        'country' => 'Netherlands',
        'latitude' => 52.36757278442383,
        'longitude' => 4.904139041900635,
        'url' => 'https://www.meetup.com/AmsterdamPHP/events/mhtvlrycclbzb/',
    ], [
        'name' => 'Post Pandemic Challenge',
        'user_group_name' => 'Boston PHP',
        'start_date_time' => '2021-09-01 00:00',
        'city' => 'Virtual',
        'country' => 'Massachusetts',
        'latitude' => 42.40721130371094,
        'longitude' => -71.38243865966797,
        'url' => 'https://www.meetup.com/bostonphp/events/278877694/',
    ], [
        'name' => 'PHP and Laravel monthly meetup',
        'user_group_name' => 'Chattanooga PHP Developers (Laravel/Drupal/Symfony/All PHP)',
        'start_date_time' => '2021-08-11 12:00',
        'city' => 'Chattanooga',
        'country' => 'Tennessee',
        'latitude' => 35.105934143066406,
        'longitude' => -85.33305358886719,
        'url' => 'https://www.meetup.com/chattanoogaphp/events/dgkvtrycclbgb/',
    ], [
        'name' => 'PHP and Laravel monthly meetup',
        'user_group_name' => 'Chattanooga PHP Developers (Laravel/Drupal/Symfony/All PHP)',
        'start_date_time' => '2021-09-01 12:00',
        'city' => 'Chattanooga',
        'country' => 'Tennessee',
        'latitude' => 35.105934143066406,
        'longitude' => -85.33305358886719,
        'url' => 'https://www.meetup.com/chattanoogaphp/events/dgkvtryccmbcb/',
    ], [
        'name' => 'PHP Meetup /Talk by.......',
        'user_group_name' => 'GroningenPHP',
        'start_date_time' => '2021-09-02 19:30',
        'city' => 'Groningen',
        'country' => 'Netherlands',
        'latitude' => 53.21424865722656,
        'longitude' => 6.569179058074951,
        'url' => 'https://www.meetup.com/GroningenPHP/events/jhvhqryccmbdb/',
    ], [
        'name' => 'PHP+Beer Downtown (IN PERSON)',
        'user_group_name' => 'The Indianapolis PHP Meetup Group',
        'start_date_time' => '2021-08-17 18:30',
        'city' => 'Indianapolis',
        'country' => 'Indiana',
        'latitude' => 39.7772331237793,
        'longitude' => -86.16178131103516,
        'url' => 'https://www.meetup.com/indyphp/events/khxhksycclbwb/',
    ], [
        'name' => '1st Laravel Munich Outdoor Meetup (sponsored by LINKS DER ISAR / AirLST)',
        'user_group_name' => 'Laravel Usergroup Munich',
        'start_date_time' => '2021-08-10 19:00',
        'city' => 'Munich',
        'country' => 'Germany',
        'latitude' => 48.147220611572266,
        'longitude' => 11.588644027709961,
        'url' => 'https://www.meetup.com/laravel-munich/events/279673620/',
    ], [
        'name' => 'PHP Adelaide User Group Meeting',
        'user_group_name' => 'PHP Adelaide',
        'start_date_time' => '2021-08-19 18:00',
        'city' => 'Adelaide',
        'country' => 'Australia',
        'latitude' => -34.928619384765625,
        'longitude' => 138.59996032714844,
        'url' => 'https://www.meetup.com/PHP-Adelaide/events/vxpcxlycclbzb/',
    ], [
        'name' => 'Looking for Speakers',
        'user_group_name' => 'Utah PHP User Group',
        'start_date_time' => '2021-08-19 19:00',
        'city' => 'Lehi',
        'country' => 'Utah',
        'latitude' => 40.44000244140625,
        'longitude' => -111.89384460449219,
        'url' => 'https://www.meetup.com/Utah-PHP-User-Group/events/vlfnprycclbzb/',
    ], [
        'name' => 'Vilnius PHP',
        'user_group_name' => 'Vilnius PHP Meetups',
        'start_date_time' => '2021-08-05 18:00',
        'city' => 'Vilnius',
        'country' => 'Lithuania',
        'latitude' => 54.677894592285156,
        'longitude' => 25.302234649658203,
        'url' => 'https://www.meetup.com/vilniusphp/events/nxbmmsycclbhb/',
    ], [
        'name' => 'Main monthly meeting',
        'user_group_name' => 'DC PHP Developer\'s Community',
        'start_date_time' => '2021-08-18 18:30',
        'city' => 'Vienna',
        'country' => 'Virginia',
        'latitude' => 38.929744720458984,
        'longitude' => -77.2470703125,
        'url' => 'https://www.meetup.com/DC-PHP/events/bszxnpycclbxb/',
    ], [
        'name' => 'BrisPHP Q3 2021 - Scaling Serverless + free talk slot',
        'user_group_name' => 'use \BNE\PHP;',
        'start_date_time' => '2021-08-31 19:00',
        'city' => 'Brisbane City',
        'country' => 'Australia',
        'latitude' => -27.47130012512207,
        'longitude' => 153.02883911132812,
        'url' => 'https://www.meetup.com/BrisPHP/events/279626702/',
    ]]);
};
