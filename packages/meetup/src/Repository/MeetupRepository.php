<?php declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Core\FileSystem\YamlFileSystem;
use Fop\Core\ValueObject\Option;
use Fop\Hydrator\ArrayToValueObjectHydrator;
use Fop\Meetup\ValueObject\Meetup;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class MeetupRepository
{
    private string $meetupsStorage;

    /**
     * @var Meetup[]
     */
    private array $meetups = [];

    public function __construct(
        private YamlFileSystem $yamlFileSystem,
        ParameterProvider $parameterProvider,
        ArrayToValueObjectHydrator $arrayToValueObjectHydrator
    ) {
        $meetupsArray = $parameterProvider->provideArrayParameter(Option::MEETUPS);

        $this->meetups = $this->createMeetups($arrayToValueObjectHydrator, $meetupsArray);
        $this->meetupsStorage = $parameterProvider->provideStringParameter(Option::MEETUPS_STORAGE);
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

    private function createMeetups(ArrayToValueObjectHydrator $arrayToValueObjectHydrator, array $meetupsArray): array
    {
        return $arrayToValueObjectHydrator->hydrateArraysToValueObject($meetupsArray, Meetup::class);
    }
}
