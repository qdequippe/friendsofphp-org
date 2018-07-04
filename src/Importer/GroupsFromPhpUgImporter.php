<?php declare(strict_types=1);

namespace Fop\Importer;

use Fop\Country\CountryResolver;
use GuzzleHttp\Client;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rinvex\Country\Country;

final class GroupsFromPhpUgImporter
{
    /**
     * @var string
     */
    private const URL_API = 'https://php.ug/api/rest/listtype/1';

    /**
     * @var CountryResolver
     */
    private $countryResolver;

    /**
     * @var Client
     */
    private $client;

    public function __construct(CountryResolver $countryResolver, Client $client)
    {
        $this->countryResolver = $countryResolver;
        $this->client = $client;
    }

    /**
     * @return mixed[]
     */
    public function import(): array
    {
        $response = $this->client->request('get', self::URL_API);

        $result = Json::decode($response->getBody(), Json::FORCE_ARRAY);

        $groups = $result['groups'];

        $meetupGroups = [];
        foreach ($groups as $group) {

            // resolve meetups.com groups only
            if (! Strings::contains($group['url'], 'meetup.com')) {
                continue;
            }

            $groupUrlName = $this->resolveGroupUrlNameFromGroupUrl($group['url']);
            $groupUrlApi = 'http://api.meetup.com/' . $groupUrlName;

            $response = $this->client->request('get', $groupUrlApi);
            $result = Json::decode($response->getBody(), Json::FORCE_ARRAY);

            $meetupGroups[] = [
                'meeutp_com_id' => $result['id'],
                'meetup_com_url' => $group['url'],
                'country' => $this->countryResolver->resolveFromGroup($group),
            ];
        }

        $meetupGroups = $this->sortByCountry($meetupGroups);

        return $this->groupMeetupsByContinent($meetupGroups);
    }

    /**
     * @param mixed[] $meetupGroups
     * @return mixed[]
     */
    private function sortByCountry(array $meetupGroups): array
    {
        uasort($meetupGroups, function (array $firstMeetupGroup, array $secondMeetupGroup) {
            return $firstMeetupGroup['country'] > $secondMeetupGroup['country'];
        });

        return $meetupGroups;
    }

    /**
     * @param mixed[] $meetupGroups
     * @return mixed[]
     */
    private function groupMeetupsByContinent(array $meetupGroups): array
    {
        $meetupGroupsByContinent = [];

        foreach ($meetupGroups as $meetupGroup) {
            $regionKey = $this->resolveRegionKey($meetupGroup);
            $meetupGroup['country'] = $meetupGroup['country'] ? $meetupGroup['country']->getName() : 'unknown';

            $meetupGroupsByContinent[$regionKey][] = $meetupGroup;
        }

        return $meetupGroupsByContinent;
    }

    /**
     * @param mixed[] $meetupGroup
     */
    private function resolveRegionKey(array $meetupGroup): string
    {
        if ($meetupGroup['country']) {
            /** @var Country $country */
            $country = $meetupGroup['country'];

            if ($country->getRegion() === null) {
                return 'unknown';
            }

            return strtolower($country->getRegion());
        }

        return 'unknown';
    }

    /**
     * @param string[] $userGroup
     */
    private function resolveGroupUrlNameFromGroupUrl(string $url): string
    {
        $url = rtrim($url, '/');

        $array = explode('/', $url);

        return $array[count($array) - 1];
    }
}
