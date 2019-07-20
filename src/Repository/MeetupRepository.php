<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Entity\Meetup;
use Fop\FileSystem\YamlFileSystem;

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

    public function __construct(string $importedMeetupsStorage, YamlFileSystem $yamlFileSystem)
    {
        $this->importedMeetupsStorage = $importedMeetupsStorage;
        $this->yamlFileSystem = $yamlFileSystem;
    }

    /**
     * @param Meetup[] $meetups
     */
    public function saveImportsToFile(array $meetups, string $category): void
    {
        $fileName = $this->importedMeetupsStorage . '/' . $category . '-imported_meetups.yaml';
        $this->saveToFileAndStorage($meetups, $fileName);
    }

    /**
     * @param Meetup[] $meetups
     */
    private function saveToFileAndStorage(array $meetups, string $storage): void
    {
        $meetups = $this->sortByStartDateTime($meetups);

        $meetupsYamlStructure = [
            'parameters' => [
                'meetups' => $this->turnsObjectsToArrays($meetups),
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($meetupsYamlStructure, $storage);
    }

    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    private function sortByStartDateTime(array $meetups): array
    {
        usort($meetups, function (Meetup $firstMeetup, Meetup $secondMeetup): int {
            return $firstMeetup->getStartDateTime() <=> $secondMeetup->getStartDateTime();
        });

        return $meetups;
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
                'user_group' => $meetup->getUserGroup(),
                'start' => $meetup->getStartDateTime()->format('Y-m-d H:i'),
                'city' => $meetup->getCity(),
                'country' => $meetup->getCountry(),
                'latitude' => $meetup->getLatitude(),
                'longitude' => $meetup->getLongitude(),
                'url' => $meetup->getUrl(),
                'tags' => $meetup->getTags(),
            ];
        }

        return $meetupsAsArray;
    }
}
