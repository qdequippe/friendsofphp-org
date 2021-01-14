<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('meetups', [[
        'name' => 'Monthly Meeting',
        'user_group_name' => 'AmsterdamPHP',
        'start_date_time' => '2021-01-21 19:00',
        'city' => 'Amsterdam',
        'country' => 'Netherlands',
        'latitude' => 52.36757278442383,
        'longitude' => 4.904139041900635,
        'url' => 'https://www.meetup.com/AmsterdamPHP/events/mhtvlrycccbcc/',
    ], [
        'name' => 'AWS Snack, Season 4',
        'user_group_name' => 'Boston PHP',
        'start_date_time' => '2021-01-31 00:00',
        'city' => 'Virtual',
        'country' => 'Massachusetts',
        'latitude' => 42.40721130371094,
        'longitude' => -71.38243865966797,
        'url' => 'https://www.meetup.com/bostonphp/events/275745182/',
    ], [
        'name' => 'PHP and Laravel monthly meetup',
        'user_group_name' => 'Chattanooga PHP Developers (Laravel/Drupal/Symfony/All PHP)',
        'start_date_time' => '2021-02-03 12:00',
        'city' => 'Chattanooga',
        'country' => 'Tennessee',
        'latitude' => 35.105934143066406,
        'longitude' => -85.33305358886719,
        'url' => 'https://www.meetup.com/chattanoogaphp/events/dgkvtryccdbfb/',
    ], [
        'name' => 'Laravel Dublin',
        'user_group_name' => 'Laravel Dublin',
        'start_date_time' => '2021-02-03 19:00',
        'city' => 'Dublin',
        'country' => 'Ireland',
        'latitude' => 53.34975814819336,
        'longitude' => -6.249186992645264,
        'url' => 'https://www.meetup.com/Laravel-Vuejs-Dublin/events/frwqhqyccdbfb/',
    ], [
        'name' => 'Nashville PHP South Lunch',
        'user_group_name' => 'Nashville PHP',
        'start_date_time' => '2021-02-02 11:30',
        'city' => 'Franklin',
        'country' => 'Tennessee',
        'latitude' => 35.957515716552734,
        'longitude' => -86.80183410644531,
        'url' => 'https://www.meetup.com/nashvillephp/events/ggbppqyccdbdb/',
    ], [
        'name' => 'PHP Adelaide User Group Meeting',
        'user_group_name' => 'PHP Adelaide',
        'start_date_time' => '2021-01-21 18:00',
        'city' => 'Adelaide',
        'country' => 'Australia',
        'latitude' => -34.928619384765625,
        'longitude' => 138.59996032714844,
        'url' => 'https://www.meetup.com/PHP-Adelaide/events/vxpcxlycccbcc/',
    ], [
        'name' => 'PHP Oxford meetup',
        'user_group_name' => 'PHP Oxford',
        'start_date_time' => '2021-01-27 19:30',
        'city' => 'Oxford',
        'country' => 'United Kingdom',
        'latitude' => 51.75237274169922,
        'longitude' => -1.2635029554367065,
        'url' => 'https://www.meetup.com/PHP-Oxford/events/vvrzwqycccbkc/',
    ], [
        'name' => 'Treffen der PHP-Usergroup Hamburg',
        'user_group_name' => 'PHP-Usergroup Hamburg (PHPUGHH)',
        'start_date_time' => '2021-02-09 18:30',
        'city' => 'Hamburg',
        'country' => 'Germany',
        'latitude' => 53.55108642578125,
        'longitude' => 9.993681907653809,
        'url' => 'https://www.meetup.com/phpughh/events/fpnmzmyccdbmb/',
    ], [
        'name' => 'Treffen der Symfony User Group Hamburg',
        'user_group_name' => 'Symfony User Group Hamburg',
        'start_date_time' => '2021-02-02 18:30',
        'city' => 'Hamburg',
        'country' => 'Germany',
        'latitude' => 53.584529876708984,
        'longitude' => 10.02515983581543,
        'url' => 'https://www.meetup.com/sfughh/events/xqdjjryccdbdb/',
    ], [
        'name' => 'Vilnius PHP',
        'user_group_name' => 'Vilnius PHP Meetups',
        'start_date_time' => '2021-02-04 19:00',
        'city' => 'Vilnius',
        'country' => 'Lithuania',
        'latitude' => 54.69993591308594,
        'longitude' => 25.31224822998047,
        'url' => 'https://www.meetup.com/vilniusphp/events/hmcgcsyccdbgb/',
    ], [
        'name' => 'Main monthly meeting',
        'user_group_name' => 'DC PHP Developer\'s Community',
        'start_date_time' => '2021-01-20 18:30',
        'city' => 'Vienna',
        'country' => 'Virginia',
        'latitude' => 38.929744720458984,
        'longitude' => -77.2470703125,
        'url' => 'https://www.meetup.com/DC-PHP/events/bszxnpycccbbc/',
    ]]);
};
