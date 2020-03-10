<?php declare(strict_types=1);

namespace Fop\Meetup;

use DateTimeInterface;
use Fop\ValueObject\Location;
use Fop\ValueObject\Meetup;

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
