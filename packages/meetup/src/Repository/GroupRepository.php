<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Core\ValueObject\Option;
use Fop\Meetup\ValueObject\Group;
use Fop\Meetup\ValueObjectFactory\GroupsFactory;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class GroupRepository
{
    /**
     * @var Group[]
     */
    private array $groups = [];

    public function __construct(ParameterProvider $parameterProvider, GroupsFactory $groupsFactory) {
        $groupsArray = $parameterProvider->provideArrayParameter(Option::GROUPS);
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
}
