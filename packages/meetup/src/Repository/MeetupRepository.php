<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\ValueObject\Meetup;
use Fop\Meetup\ValueObjectFactory\MeetupFactory;

final class MeetupRepository extends AbstractRepository
{
    /**
     * @var Meetup[]
     */
    private array $meetups = [];

    public function __construct(MeetupFactory $meetupFactory,)
    {
        $meetupsArray = $this->fetchAll();
        $this->meetups = $meetupFactory->create($meetupsArray);
    }

    /**
     * @param Meetup[] $meetups
     */
    public function saveMany(array $meetups): void
    {
        $meetupsArrays = [];
        foreach ($meetups as $meetup) {
            $meetupsArrays[] = $meetup->toArray();
        }

        $this->saveMany($meetupsArrays);
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

    public function getTable(): string
    {
        return 'meetups.json';
    }
}
