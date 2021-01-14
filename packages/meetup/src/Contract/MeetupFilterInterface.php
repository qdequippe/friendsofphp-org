<?php

declare(strict_types=1);

namespace Fop\Meetup\Contract;

use Fop\Meetup\ValueObject\Meetup;

interface MeetupFilterInterface
{
    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    public function filter(array $meetups): array;
}
