<?php declare(strict_types=1);

namespace Fop\Importer;

use Fop\Api\MeetupComApi;
use Fop\Api\PhpUgApi;
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
     * @var PhpUgApi
     */
    private $phpUgApi;
    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    public function __construct(CountryResolver $countryResolver, PhpUgApi $phpUgApi, MeetupComApi $meetupComApi)
    {
        $this->countryResolver = $countryResolver;
        $this->phpUgApi = $phpUgApi;
        $this->meetupComApi = $meetupComApi;
    }

    /**
     * @return mixed[]
     */
    public function import(): array
    {
        $groups = [];
        foreach ($this->phpUgApi->getAllGroups() as $group) {
            // resolve meetups.com groups only
            if (! Strings::contains($group['url'], 'meetup.com')) {
                continue;
            }

            $groupId = $this->meetupComApi->getIdForGroupUrl($group['url']);
            // the group doesn't exist anymore, skip it
            if ($groupId === null) {
                continue;
            }

//            dump($groupId);
//            die;
//
//            try {
//                $meetupGroupId = $this->resolveGroupIdFromUrl($group['url']);
//            } catch (ClientException $clientException) {
//                if ($clientException->getCode() === 404) {
//                    // the group doesn't exist anymore, skip it
//                    continue;
//                }
//
//                // other unknown error, show it
//                throw $clientException;
//            }

            $groups[] = [
                'name' => $group['name'],
                'meetup_com_id' => $meetupGroupId,
                'meetup_com_url' => $group['url'],
                'country' => $this->countryResolver->resolveFromGroup($group),
            ];
        }

        $groups = $this->sortByCountry($groups);

        return $this->groupMeetupsByContinent($groups);
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
}
