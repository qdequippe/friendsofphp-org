<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\ValueObject\Meetup;
use Fop\Meetup\ValueObjectFactory\MeetupFactory;

final class MeetupRepository extends AbstractRepository
{
    public function __construct(
        private readonly MeetupFactory $meetupFactory
    ) {
    }

    /**
     * @param Meetup[] $meetups
     */
    public function saveMany(array $meetups): void
    {
        foreach ($meetups as $meetup) {
            $this->insert($meetup->jsonSerialize());
        }
    }

    /**
     * @return Meetup[]
     */
    public function fetchAll(): array
    {
        $meetupsArrays = parent::fetchAll();
        $meetups = $this->meetupFactory->create($meetupsArrays);

        $meetups = array_unique($meetups);

        usort(
            $meetups,
            fn (Meetup $firstMeetup, Meetup $secondMeetup) => $firstMeetup->getStartDateTime() <=> $secondMeetup->getStartDateTime()
        );

        return $meetups;
    }

    public function getCount(): int
    {
        return count($this->fetchAll());
    }

    public function getTable(): string
    {
        return 'meetups.json';
    }

    /**
     * Delete all items
     */
    public function purge(): void
    {
        $this->jsonDb->from($this->getTable())
            ->delete()
            ->trigger();
    }
}
