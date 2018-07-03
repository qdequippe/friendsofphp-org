#!/usr/bin/env php
<?php declare(strict_types=1);

use AllFriensOfPhp\Meetup;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

$meetupsContent = file_get_contents(__DIR__ . '/../source/_data/meetups.yml');
$meetupsAsArray = Yaml::parse($meetupsContent)['parameters']['meetups'];

// map name to city
$groupNameToCity = [
    # Europe
    'BrightonPHP' => 'Brighton, UK',
    'GhentPHP' => 'Ghent, Belgium',
    'PHP.gent' => 'Ghent, Belgium',
    'Fryslân' => 'Friesland, Netherlands',
    'The Edinburgh PHP User Group' => 'Edinburgh, Scotland',
    'Bristol PHP Training' => 'Bristol, UK',
    'Aberdeen PHP' => 'Aberdeen, Scotland',
    'DeventerPHP' => 'Deventer, Netherlands',
    'Deventer' => 'Deventer, Netherlands',
    'PHP Leuven - Web Innovation Group' => 'Leuven, Belgium',
    'Groningen' => 'Groningen, Netherlands',
    'London' => 'London, UK',
    'PHP East Midlands' => 'Leicester, UK',
    'Köln-Bonn' => 'Cologne, Germany',
    'PHP USERGROUP DRESDEN' => 'Dresden, Germany',
    'Laravel Brussels' => 'Brussels, Belgium',
    'Amersfoort' => 'Amersfoort, Netherlands',
    'Glasgow' => 'Glasgow, UK',
    'North West Drupal User Group' => 'Manchester, UK',
    'Milano' => 'Milano, Italy',
    'PHP South West UK' => 'Bristol, UK',
    'Cambridge' => 'Cambridge, UK',
    'PHP Hampshire' => 'Hampshire, UK',
    'Vilnius' => 'Vilnius, Lithuania',
    'PHP Usergroup Düsseldorf' => 'Düsseldorf, Germany',

    # America
    'Peak PHP' => 'Colorado Springs, Colorado, USA',
    'Greater Lafayette Open Source Symposium PHP-SIG' => 'Lafayette, Indiana, USA',
    'Kansas City\'s PHP User Group' => 'Kansas City, Missouri, USA',
    'Nashville PHP' => 'Nashville, Tennessee, USA',
    'Frederick Web Tech' => 'Frederick, Maryland, USA',
    'Indianapolis' => 'Indianapolis, Indiana, USA',
    'Minas Gerais' => 'Minas Gerais, Brazil',
    'PHP Pernambuco' => 'Pernambuco, Brazil',
    'Rio' => 'Rio de Janeiro, Brazil',
    'San Diego PHP User Group' => 'San Diego, California, USA',
    'Atlanta' => 'Atlanta, Georgia, USA',
    'Los Angeles' => 'Los Angeles, California, USA',
    'Las Vegas' => 'Las Vegas, Nevada, USA',
    'PDX-PHP' => 'Portland, Oregon, USA',
    'Idaho PHP User Group' => 'Boise, Idaho, USA',
    'Minnesota' => 'Minnesota, USA',

    # Others
    'Melbourne' => 'Melbourne, Australia',
    'Singapore' => 'Singapore, Asia',
    'PHP Reboot' => 'Pure, India, Asia',
];

foreach ($meetupsAsArray as $key => $meetupAsArray) {
    dump($meetupAsArray);
    $groupName = $meetupAsArray['userGroup'];

    if (isset($groupNameToCity[$groupName])) {
        $meetupsAsArray[$key]['city'] = $groupNameToCity[$groupName];
    } else {
        dump($groupName);
        die;
    }
}

$yaml = [
    'parameters' => [
        'meetups' => $meetupsAsArray
    ]
];

// @todo load+save service
$yamlDump = Yaml::dump($yaml, 10, 4);
file_put_contents(__DIR__ . '/source/_data/meetups.yml', $yamlDump);
