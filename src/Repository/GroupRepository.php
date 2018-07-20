<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Entity\Group;
use Fop\FileSystem\YamlFileSystem;
use Symfony\Component\Yaml\Yaml;

final class GroupRepository
{
    /**
     * @var string
     */
    private $groupsStorage;

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
    private $importGroupsStorage;

    public function __construct(string $groupsStorage, string $importGroupsStorage, YamlFileSystem $yamlFileSystem)
    {
        $this->groupsStorage = $groupsStorage;
        $this->importGroupsStorage = $importGroupsStorage;

        $groupsArray = Yaml::parseFile($groupsStorage);
        $this->groups = $groupsArray['parameters']['groups'] ?? [];
        $this->yamlFileSystem = $yamlFileSystem;
    }

    public function saveImportToFile(array $groupsByContinent): void
    {
        $this->saveToFileAndStorage($groupsByContinent, $this->importGroupsStorage);
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
     * @return mixed[]
     */
    public function fetchByContinent(string $continent): array
    {
        return $this->groups[strtolower($continent)] ?? [];
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
