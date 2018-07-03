<?php declare(strict_types=1);

namespace AllFriensOfPhp;

use Symfony\Component\Yaml\Yaml;

final class YamlMeetupFileManager
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct(string $filePath = '')
    {
        $this->filePath = $filePath ?: __DIR__ . '/../source/_data/meetups.yml';
    }

    public function loadFromFile()
    {
    }

    /**
     * @param LocatedMeetup[] $meetups
     */
    public function saveToFile(array $meetups)
    {
        $meetupsAsArray = [];
        foreach ($meetups as $meetup) {
            // hydratation?
            $meetupsAsArray = [
                'name' => $meetup->getName(),
                'userGroup' => $meetup->getUserGroup(),
                'start' => $meetup->getStartDateTime()->format('Y-m-d H:i'),
                'city' => $meetup->getLocatoin()->getCity(),
                'country' => $meetup->getLocatoin()->getCountry(),
                'longitude' => $meetup->getLocatoin()->getLongitude(),
                'latitude' => $meetup->getLocatoin()->getLatitude(),
            ];
            dump($meetup);
            die;
        }

        $meetupsYamlStructure = [
            'parameters' => [
                'meetups' => $meetupsAsArray
            ]
        ];

        // @todo service
        $yamlDump = Yaml::dump($meetupsYamlStructure, 10, 4);
        file_put_contents($this->filePath, $yamlDump);
    }
}