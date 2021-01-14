<?php declare(strict_types=1);

namespace Fop\Group\Repository;

use Fop\Core\FileSystem\YamlFileSystem;
use Fop\Group\ValueObject\Group;
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
        \Symplify\EasyHydrator\ArrayToValueObjectHydrator $arrayToValueObjectHydrator,
        private YamlFileSystem $yamlFileSystem
    ) {
        $groupsArray = $parameterProvider->provideArrayParameter('groups');
        $this->groupsStorage = $parameterProvider->provideStringParameter('groups_storage');

        $this->groups = $this->createGroups($arrayToValueObjectHydrator, $groupsArray);
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
        $yamlStructure = [
            'parameters' => [
                'groups' => $this->turnGroupsToArrays($this->groups),
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($yamlStructure, $this->groupsStorage);
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

    /**
     * @param Group[] $groups
     * @return mixed[]
     */
    private function turnGroupsToArrays(array $groups): array
    {
        $groupsArray = [];
        foreach ($groups as $group) {
            $groupsArray[] = $group->toArray();
        }

        return $groupsArray;
    }

    /**
     * @return Group[]
     */
    private function createGroups(
        \Symplify\EasyHydrator\ArrayToValueObjectHydrator $arrayToValueObjectHydrator,
        array $groupsArray
    ): array {
        /** @var Group[] $groups */
        $groups = $arrayToValueObjectHydrator->hydrateArrays($groupsArray, Group::class);
        return $this->sortGroupsByName($groups);
    }
}
