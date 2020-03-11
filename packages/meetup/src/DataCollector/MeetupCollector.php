<?php

declare(strict_types=1);

namespace Fop\Meetup\DataCollector;

use Fop\Meetup\ValueObject\Meetup;

final class MeetupCollector
{
    /**
     * @var Meetup[]
     */
    private $meetups = [];

    /**
     * @param Meetup[] $meetups
     */
    public function addMeetups(array $meetups): void
    {
        $this->meetups = array_merge($this->meetups, $meetups);
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        return $this->meetups;
    }
}
