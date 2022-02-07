<?php

declare(strict_types=1);

namespace Fop\Meetup\DataCollector;

use Fop\Meetup\ValueObject\Meetup;
use Webmozart\Assert\Assert;

final class MeetupCollector
{
    /**
     * @var Meetup[]
     */
    private array $meetups = [];

    /**
     * @param Meetup[] $meetups
     */
    public function addMeetups(array $meetups): void
    {
        Assert::allIsAOf($meetups, Meetup::class);

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
