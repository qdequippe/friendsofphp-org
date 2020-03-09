<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\FileSystem\YamlFileSystem;
use Fop\Hydrator\ArrayToValueObjectHydrator;
use Fop\ValueObject\Meetup;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class MeetupRepository
{
    /**
     * @var string
     */
    private $meetupsStorage;

    /**
     * @var Meetup[]
     */
    private $meetups = [];

    /**
     * @var YamlFileSystem
     */
    private $yamlFileSystem;

    public function __construct(
        string $meetupsStorage,
        YamlFileSystem $yamlFileSystem,
        ParameterBagInterface $parameterBag,
        ArrayToValueObjectHydrator $arrayToValueObjectHydrator
    ) {
        $this->yamlFileSystem = $yamlFileSystem;

        $meetupsArray = (array) $parameterBag->get('meetups');
        $this->meetups = $arrayToValueObjectHydrator->hydrateArraysToValueObject($meetupsArray, Meetup::class);
        $this->meetupsStorage = $meetupsStorage;
    }

    /**
     * @param Meetup[] $meetups
     */
    public function saveImportsToFile(array $meetups): void
    {
        $this->saveToFileAndStorage($meetups, $this->meetupsStorage);
    }

    /**
     * @return Meetup[]
     */
    public function fetchAll(): array
    {
        usort($this->meetups, function (Meetup $firstMeetup, Meetup $secondMeetup) {
            return $firstMeetup->getStartDateTime() <=> $secondMeetup->getStartDateTime();
        });

        return $this->meetups;
    }

    /**
     * @param Meetup[] $meetups
     */
    private function saveToFileAndStorage(array $meetups, string $storage): void
    {
        $meetups = $this->sortByStartDateTime($meetups);

        $meetupsYamlStructure = [
            'parameters' => [
                'meetups' => $this->turnMeetupsToArrays($meetups),
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
    private function turnMeetupsToArrays(array $meetups): array
    {
        $meetupsArray = [];
        foreach ($meetups as $meetup) {
            $meetupsArray[] = $meetup->toArray();
        }

        return $meetupsArray;
    }
}
