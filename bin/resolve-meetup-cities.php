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

    # America
    'Kansas City\'s PHP User Group' => 'Kansas City, Missouri, USA',
    'Nashville PHP' => 'Nashville, Tennessee, USA',
    'Frederick Web Tech' => 'Frederick, Maryland, USA',
    'Indianapolis' => 'Indianapolis, Indiana, USA',
    'PHP Pernambuco' => 'Pernambuco, Brazilian state',

    'Aberdeen PHP' => 'Aberdeen, Scotland',

    # Others
    'Melbourne' => 'Melbourne, Australia',
    'Singapore' => 'Singapore, Asia',
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
