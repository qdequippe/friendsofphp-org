<?php declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Core\FileSystem\ParameterFilePrinter;
use Fop\Core\ValueObject\Option;
use Fop\Meetup\Arrays\ArraysConverter;
use Fop\Meetup\ValueObject\Group;
use Fop\Meetup\ValueObject\ParameterHolder;
use Fop\Meetup\ValueObjectFactory\GroupsFactory;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class GroupRepository
{
    private string $groupsStorage;

    /**
     * @var Group[]
     */
    private array $groups = [];

    public function __construct(
        ParameterProvider $parameterProvider,
        private ParameterFilePrinter $yamlFileSystem,
        private ArraysConverter $arraysConverter,
        private GroupsFactory $groupsFactory
    ) {
        $groupsArray = $parameterProvider->provideArrayParameter(Option::GROUPS);
        $this->groups = $groupsFactory->createGroups($groupsArray);

        $this->groupsStorage = $parameterProvider->provideStringParameter(Option::GROUPS_STORAGE);
    }

    /**
     * @return Group[]
     */
    public function fetchAll(): array
    {
        return $this->groups;
    }

    public function persist(): void
    {
        $groupsArray = $this->arraysConverter->turnToArrays($this->groups);
        $parameterHolder = new ParameterHolder('groups', $groupsArray);

        $this->yamlFileSystem->printParameterHolder($parameterHolder, $this->groupsStorage);
    }

//    /**
//     * @param Group[] $groups
//     * @return Group[]
//     */
//        $groups = $arrayToValueObjectHydrator->hydrateArrays($groupsArray, Group::class);
//        return $this->sortGroupsByName($groups);
//    }
}
