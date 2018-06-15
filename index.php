<?php

use AllFriensOfPhp\Meetup;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/vendor/autoload.php';

// @todo move to import command

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
foreach ($result as $event) {
    $startDateTime = DateTime::from($event['start']);

    // skip past meetups
    if ($startDateTime < $nowDateTime) { //  || $startDateTime > $nextWeekDateTime) {
        continue;
    }

    $userGroupMatch = Strings::match($event['title'], '#\[(?<userGroup>[A-Za-z ]+)\]#');
    $userGroup = $userGroupMatch['userGroup'] ?? '';

    $meetups[] = new Meetup($event['title'], $userGroup, $startDateTime);
}

// dump meetups to yaml
$meetupsAsArray = [];
foreach ($meetups as $meetup) {
    $meetupsAsArray[] = [
        'name' => $meetup->getName(),
        'userGroup' => $meetup->getUserGroup(),
        'start' => $meetup->getStartDateTime()->format('Y-m-d H:i'),
    ];
}

$yaml = ['parameters' => [
    'meetups' => $meetupsAsArray
]];

file_put_contents(__DIR__ . '/source/_data/meetups.yml', Yaml::dump($yaml));
