<?php declare(strict_types=1);

namespace Fop\Group\Repository;

use Fop\Core\FileSystem\YamlFileSystem;
use Fop\Group\ValueObject\Group;
use Fop\Hydrator\ArrayToValueObjectHydrator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class GroupRepository
{
    private string $groupsStorage;

    private YamlFileSystem $yamlFileSystem;

    /**
     * @var Group[]
     */
    private array $groups = [];

    public function __construct(
        string $groupsStorage,
        ArrayToValueObjectHydrator $arrayToValueObjectHydrator,
        ParameterBagInterface $parameterBag,
        YamlFileSystem $yamlFileSystem
    ) {
        $groupsArray = (array) $parameterBag->get('groups');

        /** @var Group[] $groups */
        $groups = $arrayToValueObjectHydrator->hydrateArraysToValueObject($groupsArray, Group::class);
        $this->groups = $this->sortGroupsByName($groups);

        $this->yamlFileSystem = $yamlFileSystem;
        $this->groupsStorage = $groupsStorage;
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
}
