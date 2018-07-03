<?php declare(strict_types=1);

namespace Fop\Repository;

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
     * @param mixed[] $meetupGroups
     */
    public function saveToFile(array $meetupGroups): void
    {
        $meetupsYamlStructure = [
            'parameters' => [
                'meetup_groups' => $meetupGroups,
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
}
