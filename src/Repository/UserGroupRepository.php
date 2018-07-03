<?php declare(strict_types=1);

namespace Fop\Repository;

use Symfony\Component\Yaml\Yaml;

final class UserGroupRepository
{
    /**
     * @var string
     */
    private $userGroupsStorage;

    public function __construct(string $userGroupsStorage)
    {
        $this->userGroupsStorage = $userGroupsStorage;
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

        // @todo service
        $yamlDump = Yaml::dump($meetupsYamlStructure, 10, 4);
        file_put_contents($this->userGroupsStorage, $yamlDump);
    }
}
