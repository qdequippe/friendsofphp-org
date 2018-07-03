#!/usr/bin/env php
<?php declare(strict_types=1);

use AllFriensOfPhp\LocatedMeetup;
use AllFriensOfPhp\Location;
use AllFriensOfPhp\YamlMeetupFileManager;
use Nette\Utils\DateTime;
use Nette\Utils\Json;

require __DIR__ . '/../vendor/autoload.php';

$client = new GuzzleHttp\Client([
    'headers' => [
        'Accept' => 'application/json'
    ]
]);


// loda from data/meetup-com-groups.yml
$groupName = '010PHP';


$nowDateTime = DateTime::from('now');

$url = sprintf('http://api.meetup.com/2/events?group_urlname=%s', $groupName);
$response = $client->get($url);

$result = Json::decode($response->getBody(), Json::FORCE_ARRAY);
$events = $result['results'];

$locatedMeetups = [];
foreach ($events as $event) {
    $startDateTime = DateTime::from(strtotime((string) $event['time']));

    // skip past meetups
    if ($startDateTime < $nowDateTime) {
        continue;
    }

    // draft event, not ready yet
    if (! isset($event['venue'])) {
        continue;
    }

    $venue = $event['venue'];
    $location = new Location($venue['city'], $venue['localized_country_name'], $venue['lon'], $venue['lat']);
    $locatedMeetups[] = new LocatedMeetup($event['name'], $event['group']['name'], $startDateTime, $location);
}

$yamlMeetupFileManager = new YamlMeetupFileManager();
$yamlMeetupFileManager->saveToFile($locatedMeetups);
