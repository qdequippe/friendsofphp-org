<?php declare(strict_types=1);

use AllFriensOfPhp\UserGroupRepository;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rinvex\Country\Country;
use Rinvex\Country\CountryLoader;

require __DIR__ . '/../vendor/autoload.php';

$client = new GuzzleHttp\Client([
    'headers' => [
        'Accept' => 'application/json',
    ],
]);

$url = sprintf('https://php.ug/api/rest/listtype/1');
$response = $client->get($url);

$result = Json::decode($response->getBody(), Json::FORCE_ARRAY);

$groups = $result['groups'];

$meetupGroups = [];
foreach ($groups as $group) {
    // resolve meetups.com groups first
    if (! Strings::contains($group['url'], 'meetup.com')) {
        continue;
    }

    $meetupGroups[] = [
        'meetup_com_url' => $group['url'],
        'country' => resolveCountry($group),
    ];
}

// sort by country
uasort($meetupGroups, function (array $firstMeetupGroup, array $secondMeetupGroup) {
    return $firstMeetupGroup['country'] > $secondMeetupGroup['country'];
});

// split by continent
$meetupGroupsByContinent = [];

foreach ($meetupGroups as $meetupGroup) {
    $country = null;
    if ($meetupGroup['country']) {
        /** @var Country $country */
        $country = $meetupGroup['country'];

        if ($country->getRegion() === null) {
            $regionKey = 'unknown';
        } else {
            $regionKey = strtolower($country->getRegion());
        }
    } else {
        $regionKey = 'unknown';
    }

    $meetupGroup['country'] = $country ? $country->getName() : 'unknown';
    $meetupGroupsByContinent[$regionKey][] = $meetupGroup;
}

function resolveCountry(array $group): ?Country
{
    if ($group['country']) {
        return CountryLoader::country($group['country']);
    }

    // detect city + country from latitude/longitude
    $latitude = $group['latitude'];
    $longitude = $group['longitude'];

    $geocode = file_get_contents(sprintf(
        'http://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&sensor=false',
        $latitude,
        $longitude
    ));

    $geocodeJson = Json::decode($geocode, Json::FORCE_ARRAY);

    if ($geocodeJson['status'] !== 'OK') {
        return null;
    }

    foreach ($geocodeJson['results'][0]['address_components'] as $addressComponent) {
        if (in_array('country', $addressComponent['types'], true)) {
            return CountryLoader::country($addressComponent['short_name']);
        }
    }

    return null;
}

$userGroupRepository = (new UserGroupRepository());
$userGroupRepository->saveToFile($meetupGroupsByContinent);
