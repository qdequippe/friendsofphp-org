<?php

declare(strict_types=1);

namespace Fop\Meetup\Filter;

use Fop\Meetup\ValueObject\Meetup;
use Nette\Utils\DateTime;

final class MeetupFilter
{
    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    public function filter(array $meetups): array
    {
        $futureMeetups = $this->filterOutPastMeetups($meetups);
        return $this->filterTooFarMeetups($futureMeetups);
    }

    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    private function filterOutPastMeetups(array $meetups): array
    {
        return array_filter(
            $meetups,
            fn (Meetup $meetup): bool => $meetup->getUtcStartDateTime() > DateTime::from('now')
        );
    }

    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    private function filterTooFarMeetups(array $meetups): array
    {
        // remove meetups too far in the future - mostly automatically generated without any content
        $maxForecastDateTime = DateTime::from('+ 30 days');

        return array_filter(
            $meetups,
            fn (Meetup $meetup): bool => $meetup->getUtcStartDateTime() <= $maxForecastDateTime
        );
    }
}
