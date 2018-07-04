<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Entity\Meetup;
use Fop\FileSystem\YamlFileSystem;

final class MeetupRepository
{
    /**
     * @var string
     */
    private $meetupsStorage;

    /**
     * @var YamlFileSystem
     */
    private $yamlFileSystem;

    public function __construct(string $meetupsStorage, YamlFileSystem $yamlFileSystem)
    {
        $this->meetupsStorage = $meetupsStorage;
        $this->yamlFileSystem = $yamlFileSystem;
    }

    /**
     * @param Meetup[] $meetups
     */
    public function saveToFile(array $meetups): void
    {
        $meetupsAsArray = [];
        foreach ($meetups as $meetup) {
            $meetupsAsArray[] = [
                'name' => $meetup->getName(),
                'userGroup' => $meetup->getUserGroup(),
                'start' => $meetup->getStartDateTime()->format('Y-m-d H:i'),
                'city' => $meetup->getLocatoin()->getCity(),
                'country' => $meetup->getLocatoin()->getCountry(),
                'longitude' => $meetup->getLocatoin()->getLongitude(),
                'latitude' => $meetup->getLocatoin()->getLatitude(),
            ];
        }

        $meetupsYamlStructure = [
            'parameters' => [
                'meetups' => $meetupsAsArray,
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($meetupsYamlStructure, $this->meetupsStorage);
    }
}
