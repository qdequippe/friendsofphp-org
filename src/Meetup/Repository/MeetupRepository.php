<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\ValueObject\Meetup;

/**
 * @extends AbstractRepository<Meetup>
 */
final class MeetupRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Meetup::class);
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
        $meetups = parent::fetchAll();

        usort(
            $meetups,
            fn (Meetup $firstMeetup, Meetup $secondMeetup): int => $firstMeetup->getUtcStartDateTime() <=> $secondMeetup->getUtcStartDateTime()
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
}
