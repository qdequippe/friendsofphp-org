<?php declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Core\FileSystem\YamlFileSystem;
use Fop\Hydrator\ArrayToValueObjectHydrator;
use Fop\Meetup\ValueObject\Meetup;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class MeetupRepository
{
    private string $meetupsStorage;

    private YamlFileSystem $yamlFileSystem;

    /**
     * @var Meetup[]
     */
    private array $meetups = [];

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
        usort(
            $this->meetups,
            fn (Meetup $firstMeetup, Meetup $secondMeetup) => $firstMeetup->getStartDateTime() <=> $secondMeetup->getStartDateTime()
        );

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
        usort(
            $meetups,
            fn (Meetup $firstMeetup, Meetup $secondMeetup): int => $firstMeetup->getStartDateTime() <=> $secondMeetup->getStartDateTime()
        );

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
