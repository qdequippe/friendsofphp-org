<?php declare(strict_types=1);

namespace Fop\Meetup;

use DateTimeInterface;
use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Fop\Tag\MeetupTagResolver;
use Location\Coordinate;
use Nette\Utils\DateTime;

final class MeetupFactory
{
    /**
     * @var MeetupTagResolver
     */
    private $meetupTagResolver;

    public function __construct(MeetupTagResolver $meetupTagResolver)
    {
        $this->meetupTagResolver = $meetupTagResolver;
    }

    public function create(
        string $name,
        string $groupName,
        DateTimeInterface $startDateTime,
        Location $location,
        string $url
    ): Meetup {
        $tags = $this->meetupTagResolver->resolveFromName($name, $groupName);

        return new Meetup($name, $groupName, $startDateTime, $location, $url, $tags);
    }

    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): Meetup
    {
        $coordinate = new Coordinate($data['latitude'], $data['longitude']);
        $location = new Location($data['city'], $data['country'], $coordinate);

        $startDateTime = DateTime::from($data['start']);

        return $this->create($data['name'], $data['userGroup'], $startDateTime, $location, $data['url']);
    }
}
