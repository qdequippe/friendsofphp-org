<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\ValueObject\Group;
use Fop\Meetup\ValueObjectFactory\GroupsFactory;

final class GroupRepository extends AbstractRepository
{
    /**
     * @var Group[]
     */
    private array $groups = [];

    public function __construct(GroupsFactory $groupsFactory)
    {
        $groupsArray = $this->fetchAll();
        $this->groups = $groupsFactory->create($groupsArray);
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getCount(): int
    {
        return count($this->groups);
    }

    /**
     * @return Group[]
     */
    public function fetchAll(): array
    {
        return $this->groups;
    }

    public function getTable(): string
    {
        return 'meetups.json';
    }
}
