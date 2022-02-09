<?php

declare(strict_types=1);

namespace Fop\Meetup\Mapper;

use Fop\Meetup\ValueObject\Group;
use Webmozart\Assert\Assert;

/**
 * @see \Fop\Meetup\Tests\Mapper\GroupsMapperTest
 */
final class GroupsMapper
{
    /**
     * @param array<string, mixed> $groupsArray
     * @return Group[]
     */
    public function mapArraysToObjects(array $groupsArray): array
    {
        $groups = [];
        foreach ($groupsArray as $groupArray) {
            $groups[] = Group::fromArray($groupArray);
        }

        Assert::allIsAOf($groups, Group::class);

        return $this->sortGroupsByName($groups);
    }

    /**
     * @param Group[] $groups
     * @return Group[]
     */
    private function sortGroupsByName(array $groups): array
    {
        usort(
            $groups,
            fn (Group $firstGroup, Group $secondGroup) => $firstGroup->getName() <=> $secondGroup->getName()
        );

        return $groups;
    }
}
