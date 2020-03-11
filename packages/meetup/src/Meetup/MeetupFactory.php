<?php declare(strict_types=1);

namespace Fop\Meetup\Meetup;

use DateTimeInterface;
use Fop\Meetup\ValueObject\Location;
use Fop\Meetup\ValueObject\Meetup;

final class MeetupFactory
{
    public function create(
        string $name,
        string $groupName,
        DateTimeInterface $startDateTime,
        Location $location,
        string $url
    ): Meetup {
        return new Meetup($name, $groupName, $startDateTime, $location, $url);
    }
}
