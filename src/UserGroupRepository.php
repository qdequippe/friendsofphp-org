<?php declare(strict_types=1);

namespace AllFriensOfPhp;

use Symfony\Component\Yaml\Yaml;

final class UserGroupRepository
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct(string $filePath = '')
    {
        $this->filePath = $filePath ?: __DIR__ . '/../source/_data/user_groups.yml';
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
        file_put_contents($this->filePath, $yamlDump);
    }
}
