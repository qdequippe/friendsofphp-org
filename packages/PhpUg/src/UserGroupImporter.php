<?php declare(strict_types=1);

namespace Fop\PhpUg;

use Fop\Country\CountryResolver;
use Fop\Entity\Group;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\PhpUg\Api\PhpUgApi;
use Nette\Utils\Strings;

final class UserGroupImporter
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
     * @return Group[]
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

        return $this->sortByCountry($groups);
    }

    /**
     * @param mixed[] $groups
     * @return mixed[]
     */
    private function sortByCountry(array $groups): array
    {
        uasort($groups, function (Group $firstGroup, Group $secondGroup) {
            return $firstGroup->getCountry() > $secondGroup->getCountry();
        });

        return $groups;
    }
}
