<?php declare(strict_types=1);

namespace Fop\Repository;

use Symfony\Component\Yaml\Yaml;

final class UserGroupRepository
{
    /**
     * @var string
     */
    private $userGroupStorage;

    public function __construct(string $userGroupStorage)
    {
        $this->userGroupStorage = $userGroupStorage;
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
        file_put_contents($this->userGroupStorage, $yamlDump);
    }
}
