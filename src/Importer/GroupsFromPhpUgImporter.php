<?php declare(strict_types=1);

namespace Fop\Importer;

use Fop\Api\MeetupComApi;
use Fop\Api\PhpUgApi;
use Fop\Country\CountryResolver;
use Fop\Entity\Group;
use Nette\Utils\Strings;
use Rinvex\Country\Country;

final class GroupsFromPhpUgImporter
{
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
     * @return Group[][]
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

            $groups[] = new Group(
                $group['name'],
                $groupId,
                $group['url'],
                $this->countryResolver->resolveFromGroup($group)
            );
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
