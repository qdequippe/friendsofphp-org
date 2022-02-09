<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\ValueObject\Group;

/**
 * @extends AbstractRepository<Group>
 */
final class GroupRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Group::class);
    }

    /**
     * @return Group[]
     */
    public function fetchAll(): array
    {
        $groupsArray = parent::fetchAll();

        return $this->sortGroupsByName($groupsArray);
    }

    public function getCount(): int
    {
        return count($this->fetchAll());
    }

    public function getTable(): string
    {
        return 'groups.json';
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
