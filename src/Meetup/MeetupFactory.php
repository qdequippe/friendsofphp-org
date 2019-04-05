<?php declare(strict_types=1);

namespace Fop\Meetup;
use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Location\Coordinate;
use Nette\Utils\DateTime;
final class MeetupFactory
{
    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): Meetup
    {
        $coordinate = new Coordinate($data['latitude'], $data['longitude']);
        $location = new Location($data['city'], $data['country'], $coordinate);
        return new Meetup($data['name'], $data['userGroup'], DateTime::from($data['start']), $location, $data['url']);
    }
}
