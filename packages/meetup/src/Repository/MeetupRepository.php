<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Core\FileSystem\ParameterFilePrinter;
use Fop\Core\ValueObject\Option;
use Fop\Meetup\Arrays\ArraysConverter;
use Fop\Meetup\ValueObject\Meetup;
use Fop\Meetup\ValueObject\ParameterHolder;
use Fop\Meetup\ValueObjectFactory\MeetupFactory;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class MeetupRepository
{
    private string $meetupsStorage;

    /**
     * @var Meetup[]
     */
    private array $meetups = [];

    public function __construct(
        private ParameterFilePrinter $parameterFilePrinter,
        ParameterProvider $parameterProvider,
        private ArraysConverter $arraysConverter,
        MeetupFactory $meetupFactory,
    ) {
        $meetupsArray = $parameterProvider->provideArrayParameter(Option::MEETUPS);
        $this->meetups = $meetupFactory->create($meetupsArray);
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

    public function getCount(): int
    {
        return count($this->fetchAll());
    }

    /**
     * @param Meetup[] $meetups
     */
    private function saveToFileAndStorage(array $meetups, string $storage): void
    {
        $meetupsArray = $this->arraysConverter->turnToArrays($meetups);
        $parameterHolder = new ParameterHolder(Option::MEETUPS, $meetupsArray);
        $this->parameterFilePrinter->printParameterHolder($parameterHolder, $storage);
    }
}
