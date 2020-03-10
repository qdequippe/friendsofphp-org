<?php declare(strict_types=1);

namespace Fop\Contract;

use Fop\ValueObject\Meetup;

interface MeetupFilterInterface
{
    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    public function filter(array $meetups): array;
}
