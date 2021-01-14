<?php declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Core\FileSystem\ParameterFilePrinter;
use Fop\Core\ValueObject\Option;
use Fop\Meetup\Arrays\ArraysConverter;
use Fop\Meetup\ValueObject\Meetup;
use Fop\Meetup\ValueObject\ParameterHolder;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class MeetupRepository
{
    private string $meetupsStorage;

    /**
     * @var Meetup[]
     */
    private array $meetups = [];

    public function __construct(
        private ParameterFilePrinter $yamlFileSystem,
        ParameterProvider $parameterProvider,
        ArrayToValueObjectHydrator $arrayToValueObjectHydrator,
        private ArraysConverter $arraysConverter
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

        $meetupsArray = $this->arraysConverter->turnToArrays($meetups);
        $parameterHolder = new ParameterHolder('meetups', $meetupsArray);
        $this->yamlFileSystem->printParameterHolder($parameterHolder, $storage);
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

    private function createMeetups(ArrayToValueObjectHydrator $arrayToValueObjectHydrator, array $meetupsArray): array
    {
        return $arrayToValueObjectHydrator->hydrateArrays($meetupsArray, Meetup::class);
    }
}
