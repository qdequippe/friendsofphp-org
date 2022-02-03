<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\ValueObject\Group;
use Fop\Meetup\ValueObjectFactory\GroupsFactory;

final class GroupRepository extends AbstractRepository
{
    public function __construct(
        private readonly GroupsFactory $groupsFactory
    ) {
    }

    /**
     * @return Group[]
     */
    public function fetchAll(): array
    {
        $groupsArray = parent::fetchAll();
        return $this->groupsFactory->create($groupsArray);
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
