<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\Mapper\GroupsMapper;
use Fop\Meetup\ValueObject\Group;

final class GroupRepository extends AbstractRepository
{
    public function __construct(
        private readonly GroupsMapper $groupsMapper
    ) {
    }

    /**
     * @return Group[]
     */
    public function fetchAll(): array
    {
        $groupsArray = parent::fetchAll();
        return $this->groupsMapper->mapArraysToObjects($groupsArray);
    }

    public function getCount(): int
    {
        return count($this->fetchAll());
    }

    public function getTable(): string
    {
        return 'groups.json';
    }
}
