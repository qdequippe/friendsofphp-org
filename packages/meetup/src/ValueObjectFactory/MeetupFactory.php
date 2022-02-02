<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObjectFactory;

use Fop\Meetup\ValueObject\Meetup;

/**
 * @see \Fop\Meetup\Tests\ValueObjectFactory\MeetupFactoryTest
 */
final class MeetupFactory
{
    /**
     * @param mixed[] $meetupsArray
     * @return Meetup[]
     */
    public function create(array $meetupsArray): array
    {
        $meetups = [];

        foreach ($meetupsArray as $meetupArray) {
            $meetups[] = Meetup::fromArray($meetupArray);
        }

        return $this->sortByStartDateTime($meetups);
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
}
