<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Entity\Group;
use Fop\FileSystem\YamlFileSystem;
use Symplify\PackageBuilder\Yaml\ParametersMergingYamlLoader;

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
        ParametersMergingYamlLoader $parametersMergingYamlLoader
    ) {
        $this->importedGroupsStorage = $importedGroupsStorage;

        $parameterBag = $parametersMergingYamlLoader->loadParameterBagFromFile($groupsStorage);
        $this->groups = $parameterBag->get('groups');

        $this->yamlFileSystem = $yamlFileSystem;
    }

    /**
     * @param mixed[] $groupsByContinent
     */
    public function saveImportToFile(array $groupsByContinent): void
    {
        $this->saveToFileAndStorage($groupsByContinent, $this->importedGroupsStorage);
    }

    /**
     * @return mixed[]
     */
    public function fetchByContinent(string $continent): array
    {
        return $this->groups[strtolower($continent)] ?? [];
    }

    /**
     * @param Group[][] $groupsByContinent
     */
    private function saveToFileAndStorage(array $groupsByContinent, string $storage): void
    {
        $meetupsYamlStructure = [
            'parameters' => [
                'groups' => $this->turnObjectsToArrays($groupsByContinent),
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($meetupsYamlStructure, $storage);
    }

    /**
     * @param Group[][] $groupsByContinent
     * @return mixed[][]
     */
    private function turnObjectsToArrays(array $groupsByContinent): array
    {
        $arrayGroupsByContinent = [];

        foreach ($groupsByContinent as $continent => $groups) {
            $arrayGroups = [];
            /** @var Group $group */
            foreach ($groups as $group) {
                $arrayGroups[] = [
                    'name' => $group->getName(),
                    'meetup_com_id' => $group->getMeetupComId(),
                    'meetup_com_url' => $group->getMeetupComUrl(),
                    'country' => $group->getCountry() ? $group->getCountry()->getName() : 'unknown',
                ];
            }

            $arrayGroupsByContinent[$continent] = $arrayGroups;
        }

        return $arrayGroupsByContinent;
    }
}
