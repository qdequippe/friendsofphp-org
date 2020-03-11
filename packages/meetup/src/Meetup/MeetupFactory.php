<?php declare(strict_types=1);

namespace Fop\Meetup\Meetup;

use DateTimeInterface;
use Fop\Core\ValueObject\Location;
use Fop\Core\ValueObject\Meetup;

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
