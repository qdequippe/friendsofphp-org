<?php declare(strict_types=1);

namespace Fop\Group\Repository;

use Fop\Core\ValueObject\Group;
use Fop\Core\FileSystem\YamlFileSystem;
use Fop\Hydrator\ArrayToValueObjectHydrator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class GroupRepository
{
    /**
     * @var string
     */
    private $groupsStorage;

    /**
     * @var Group[]
     */
    private $groups = [];

    /**
     * @var YamlFileSystem
     */
    private $yamlFileSystem;

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
        usort($groups, function (Group $firstGroup, Group $secondGroup) {
            return $firstGroup->getName() <=> $secondGroup->getName();
        });

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
