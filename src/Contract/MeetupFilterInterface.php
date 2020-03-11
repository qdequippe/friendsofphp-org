<?php declare(strict_types=1);

namespace Fop\Core\Contract;

use Fop\Core\ValueObject\Meetup;

interface MeetupFilterInterface
{
    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    public function filter(array $meetups): array;
}
