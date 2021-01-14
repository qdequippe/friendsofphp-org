<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObjectFactory;

use Fop\Meetup\ValueObject\Meetup;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

/**
 * @see \Fop\Meetup\Tests\ValueObjectFactory\MeetupFactoryTest
 */
final class MeetupFactory
{
    private ArrayToValueObjectHydrator $arrayToValueObjectHydrator;

    public function __construct(ArrayToValueObjectHydrator $arrayToValueObjectHydrator)
    {
        $this->arrayToValueObjectHydrator = $arrayToValueObjectHydrator;
    }

    /**
     * @param mixed[] $meetupsArray
     * @return Meetup[]
     */
    public function create(array $meetupsArray): array
    {
        /** @var Meetup[] $meetups */
        $meetups = $this->arrayToValueObjectHydrator->hydrateArrays($meetupsArray, Meetup::class);
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
