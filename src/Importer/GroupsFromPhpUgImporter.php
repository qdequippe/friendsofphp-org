<?php declare(strict_types=1);

namespace Fop\Importer;

use Fop\PhpUg\Api\PhpUgApi;
use Fop\Country\CountryResolver;
use Fop\Entity\Group;
use Fop\MeetupCom\Api\MeetupComApi;
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

        // resolve country to string
        foreach ($groups as $key => $group) {
            $groups[$key]['country'] = $group['country'] ? $group['country']->getName() : 'unknown';
        }

        return $groups;
    }

    /**
     * @param mixed[] $groups
     * @return mixed[]
     */
    private function sortByCountry(array $groups): array
    {
        uasort($groups, function (array $firstGroup, array $secondGroup) {
            return $firstGroup['country'] > $secondGroup['country'];
        });

        return $groups;
    }
}
