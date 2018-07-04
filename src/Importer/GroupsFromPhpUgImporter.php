<?php declare(strict_types=1);

namespace Fop\Importer;

use Fop\Country\CountryResolver;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rinvex\Country\Country;

final class GroupsFromPhpUgImporter
{
    /**
     * e.g. http://api.meetup.com/dallasphp
     * @var string
     */
    private const API_GROUP_DETAIL_URL = 'http://api.meetup.com/';

    /**
     * @var string
     */
    private const API_ALL_GROUPS_URL = 'https://php.ug/api/rest/listtype/1';

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
        $response = $this->client->request('get', self::API_ALL_GROUPS_URL);

        $groups = $this->getResponseItem($response, 'groups');

        $meetupGroups = [];
        foreach ($groups as $group) {
            // resolve meetups.com groups only
            if (! Strings::contains($group['url'], 'meetup.com')) {
                continue;
            }

            try {
                $meetupGroupId = $this->resolveGroupIdFromUrl($group['url']);
            } catch (ClientException $clientException) {
                if ($clientException->getCode() === 404) {
                    // the group doesn't exist anymore, skip it
                    continue;
                }
            }

            $meetupGroups[] = [
                'name' => $group['name'],
                'meeutp_com_id' => $meetupGroupId,
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

    private function resolveGroupIdFromUrl(string $groupUrl): int
    {
        $groupUrlName = $this->resolveGroupUrlNameFromGroupUrl($groupUrl);

        $response = $this->client->request('get', self::API_GROUP_DETAIL_URL . $groupUrlName);

        return $this->getResponseItem($response, 'id');
    }

    private function resolveGroupUrlNameFromGroupUrl(string $url): string
    {
        $url = rtrim($url, '/');

        $array = explode('/', $url);

        return $array[count($array) - 1];
    }

    /**
     * @return mixed
     */
    private function getResponseItem(Response $response, string $name)
    {
        $result = Json::decode($response->getBody(), Json::FORCE_ARRAY);

        return $result[$name];
    }
}
