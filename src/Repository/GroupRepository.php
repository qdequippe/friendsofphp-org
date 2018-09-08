<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Entity\Group;
use Fop\FileSystem\YamlFileSystem;
use Symplify\PackageBuilder\Yaml\ParameterMergingYamlLoader;

final class GroupRepository
{
    /**
     * @var mixed[]
     */
    private $groups = [];

    /**
     * @var YamlFileSystem
     */
    private $yamlFileSystem;

    /**
     * @var string
     */
    private $importedGroupsStorage;

    public function __construct(
        string $groupsStorage,
        string $importedGroupsStorage,
        YamlFileSystem $yamlFileSystem,
        ParameterMergingYamlLoader $parameterMergingYamlLoader
    ) {
        $this->importedGroupsStorage = $importedGroupsStorage;

        $parameterBag = $parameterMergingYamlLoader->loadParameterBagFromFile($groupsStorage);
        $this->groups = $parameterBag->get('groups');

        $this->yamlFileSystem = $yamlFileSystem;
    }

    /**
     * @param mixed[] $groups
     */
    public function saveImportToFile(array $groups): void
    {
        $this->saveToFileAndStorage($groups, $this->importedGroupsStorage);
    }

    /**
     * @return mixed[]
     */
    public function fetchAll(): array
    {
        return $this->groups;
    }

    /**
     * @param Group[] $groups
     */
    private function saveToFileAndStorage(array $groups, string $storage): void
    {
        $meetupsYamlStructure = [
            'parameters' => [
                'groups' => $this->turnObjectsToArrays($groups),
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($meetupsYamlStructure, $storage);
    }

    /**
     * @param Group[] $groups
     * @return mixed[][]
     */
    private function turnObjectsToArrays(array $groups): array
    {
        $arrayGroups = [];

        /** @var Group $group */
        foreach ($groups as $group) {
            $arrayGroups[] = [
                'name' => $group->getName(),
                'meetup_com_id' => $group->getMeetupComId(),
                'meetup_com_url' => $group->getMeetupComUrl(),
                'country' => $group->getCountry(),
            ];
        }

        return $arrayGroups;
    }
}
