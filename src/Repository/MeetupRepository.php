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
    /**
     * @var string
     */
    private $importedMeetupsStorage;

    public function __construct(string $meetupsStorage, string $importedMeetupsStorage, YamlFileSystem $yamlFileSystem)
    {
        $this->meetupsStorage = $meetupsStorage;
        $this->importedMeetupsStorage = $importedMeetupsStorage;
        $this->yamlFileSystem = $yamlFileSystem;
    }

    /**
     * @param Meetup[] $meetups
     */
    public function saveImportsToFile(array $meetups): void
    {
        $this->saveToFileAndStorage($meetups, $this->importedMeetupsStorage);
    }

    /**
     * @param Meetup[] $meetups
     */
    private function saveToFileAndStorage(array $meetups, string $storage): void
    {
        $meetupsYamlStructure = [
            'parameters' => [
                'meetups' => $this->turnsObjectsToArrays($meetups),
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($meetupsYamlStructure, $storage);
    }

    private function turnsObjectsToArrays(array $meetups): array
    {
        $meetupsAsArray = [];
        foreach ($meetups as $meetup) {
            $meetupsAsArray[] = [
                'name' => $meetup->getName(),
                'userGroup' => $meetup->getUserGroup(),
                'start' => $meetup->getStartDateTime()->format('Y-m-d H:i'),
                'end' => ($meetup->getEndDateTime() !== null) ? $meetup->getEndDateTime()->format('Y-m-d H:i') : null,
                'city' => $meetup->getLocatoin()->getCity(),
                'country' => $meetup->getLocatoin()->getCountry(),
                'longitude' => $meetup->getLocatoin()->getLongitude(),
                'latitude' => $meetup->getLocatoin()->getLatitude(),
                'url' => $meetup->getUrl(),
            ];
        }
        return $meetupsAsArray;
    }
}
