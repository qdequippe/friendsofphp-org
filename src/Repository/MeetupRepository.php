<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Entity\Meetup;
use Fop\FileSystem\YamlFileSystem;
use Fop\Nomad\Exception\ConfigurationException;
use Fop\Nomad\Factory\MeetupFactory;

final class MeetupRepository
{
    /**
     * @var string
     */
    private $importedMeetupsStorage;

    /**
     * @var YamlFileSystem
     */
    private $yamlFileSystem;

    /**
     * @var MeetupFactory
     */
    private $meetupFactory;

    public function __construct(
        string $importedMeetupsStorage,
        YamlFileSystem $yamlFileSystem,
        MeetupFactory $meetupFactory
    ) {
        $this->importedMeetupsStorage = $importedMeetupsStorage;
        $this->yamlFileSystem = $yamlFileSystem;
        $this->meetupFactory = $meetupFactory;
    }

    /**
     * @param Meetup[] $meetups
     */
    public function saveImportsToFile(array $meetups): void
    {
        $this->saveToFileAndStorage($meetups, $this->importedMeetupsStorage);
    }

    /**
     * @return Meetup[]
     */
    public function fetchAllAsObjects(): array
    {
        $this->ensureStorageExists();

        $data = $this->yamlFileSystem->loadFileToArray($this->importedMeetupsStorage);
        $meetups = $data['parameters']['meetups'] ?? [];

        return $this->turnsArraysToObjects($meetups);
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

    private function ensureStorageExists(): void
    {
        if (file_exists($this->importedMeetupsStorage)) {
            return;
        }

        throw new ConfigurationException(sprintf(
            'File "%s" is missing. Run "bin/console import" first.',
            $this->importedMeetupsStorage
        ));
    }

    /**
     * @param mixed[] $meetups
     * @return Meetup[]
     */
    private function turnsArraysToObjects(array $meetups): array
    {
        $meetupsAsObjects = [];

        foreach ($meetups as $meetup) {
            $meetupsAsObjects[] = $this->meetupFactory->createFromArray($meetup);
        }

        return $meetupsAsObjects;
    }

    /**
     * @param Meetup[] $meetups
     * @return mixed[]
     */
    private function turnsObjectsToArrays(array $meetups): array
    {
        $meetupsAsArray = [];

        foreach ($meetups as $meetup) {
            $meetupsAsArray[] = [
                'name' => $meetup->getName(),
                'userGroup' => $meetup->getUserGroup(),
                'start' => $meetup->getStartDateTime()->format('Y-m-d H:i'),
                'city' => $meetup->getCity(),
                'country' => $meetup->getCountry(),
                'latitude' => $meetup->getLatitude(),
                'longitude' => $meetup->getLongitude(),
                'url' => $meetup->getUrl(),
            ];
        }

        return $meetupsAsArray;
    }
}
