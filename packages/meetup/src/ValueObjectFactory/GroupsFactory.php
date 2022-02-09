<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObjectFactory;

use Fop\Meetup\ValueObject\Group;
use Webmozart\Assert\Assert;

/**
 * @see \Fop\Meetup\Tests\ValueObjectFactory\GroupsFactoryTest
 */
final class GroupsFactory
{
    /**
     * @param array<string, mixed> $groupsArray
     * @return Group[]
     */
    public function create(array $groupsArray): array
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
