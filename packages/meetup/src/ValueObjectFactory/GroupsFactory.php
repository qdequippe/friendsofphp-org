<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObjectFactory;

use Fop\Meetup\ValueObject\Group;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

/**
 * @see \Fop\Meetup\Tests\ValueObjectFactory\GroupsFactoryTest
 */
final class GroupsFactory
{
    private ArrayToValueObjectHydrator $arrayToValueObjectHydrator;

    public function __construct(ArrayToValueObjectHydrator $arrayToValueObjectHydrator)
    {
        $this->arrayToValueObjectHydrator = $arrayToValueObjectHydrator;
    }

    /**
     * @param mixed[] $groupsArray
     * @return Group[]
     */
    public function create(array $groupsArray): array
    {
        /** @var Group[] $groups */
        $groups = $this->arrayToValueObjectHydrator->hydrateArrays($groupsArray, Group::class);
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
