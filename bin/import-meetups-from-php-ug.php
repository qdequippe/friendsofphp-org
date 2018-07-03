#!/usr/bin/env php
<?php declare(strict_types=1);

use AllFriensOfPhp\Meetup;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

$client = new GuzzleHttp\Client([
    'headers' => [
        'Accept' => 'application/json'
    ]
]);
$result = $client->get('https://php.ug/api/v1/calendar/list');

$result = Json::decode($result->getBody(), Json::FORCE_ARRAY);

//$nextWeekDateTime = DateTime::from('+1 week');
$nowDateTime = DateTime::from('now');

/** @var Meetup[] $meetups */
$meetups = [];

/** @var string[] $links */
$links = [];
foreach ($result as $event) {
    $startDateTime = DateTime::from($event['start']);

    // skip past meetups
    if ($startDateTime < $nowDateTime) {
        continue;
    }

    // @todo resolve user group city
    $userGroupMatch = Strings::match($event['title'], '#\[(?<userGroup>[\w-\'öüãâ.&\(\) ]+)\]#');

    if (! isset($userGroupMatch['userGroup'])) {
        dump($event['title']);
        dump($userGroupMatch);
        die;
    }

    $links[] = 'https://meetup.com/' . str_replace(' ', '-', $userGroupMatch['userGroup']);

//    $userGroup = $userGroupMatch['userGroup'] ?? '';

//    $meetups[] = new Meetup($event['title'], $userGroup, $startDateTime);
}

$links = array_unique($links);

$groupsYamlFile = [
    'parameters' => [
        'groups' => [
            $links
        ]
    ]
];

dump($links);

$yamlDump = Yaml::dump($groupsYamlFile, 10, 4);

file_put_contents(__DIR__ . '/../source/_data/groups.yml', $yamlDump);


die;

// dump meetups to yaml
$meetupsAsArray = [];
foreach ($meetups as $meetup) {
    $meetupsAsArray[] = [
        'name' => $meetup->getName(),
        'userGroup' => $meetup->getUserGroup(),
        'start' => $meetup->getStartDateTime()->format('Y-m-d H:i'),
    ];
}

die;

$yaml = ['parameters' => [
    'meetups' => $meetupsAsArray
]];

$yamlDump = Yaml::dump($yaml, 10, 4);

file_put_contents(__DIR__ . '/source/_data/meetups.yml', $yamlDump);
