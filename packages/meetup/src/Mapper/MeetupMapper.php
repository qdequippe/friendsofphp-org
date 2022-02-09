<?php

declare(strict_types=1);

namespace Fop\Meetup\Mapper;

use Fop\Meetup\ValueObject\Meetup;
use Webmozart\Assert\Assert;

/**
 * @see \Fop\Meetup\Tests\Mapper\MeetupMapperTest
 */
final class MeetupMapper
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

        Assert::allIsAOf($meetups, Meetup::class);

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
