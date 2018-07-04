<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Entity\Group;
use Fop\FileSystem\YamlFileSystem;
use Symfony\Component\Yaml\Yaml;

final class UserGroupRepository
{
    /**
     * @var string
     */
    private $userGroupsStorage;

    /**
     * @var mixed[]
     */
    private $userGroups = [];

    /**
     * @var YamlFileSystem
     */
    private $yamlFileSystem;

    public function __construct(string $userGroupsStorage, YamlFileSystem $yamlFileSystem)
    {
        $this->userGroupsStorage = $userGroupsStorage;

        $userGroupsArray = Yaml::parseFile($userGroupsStorage);
        $this->userGroups = $userGroupsArray['parameters']['meetup_groups'] ?? [];
        $this->yamlFileSystem = $yamlFileSystem;
    }

    /**
     * @param Group[][] $groupsByContinent
     */
    public function saveToFile(array $groupsByContinent): void
    {
        $meetupsYamlStructure = [
            'parameters' => [
                'meetup_groups' => $this->turnObjectsToArrays($groupsByContinent),
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($meetupsYamlStructure, $this->userGroupsStorage);
    }

    /**
     * @return mixed[]
     */
    public function fetchByContinent(string $continent): array
    {
        return $this->userGroups[strtolower($continent)] ?? [];
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
