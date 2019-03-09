<?php declare(strict_types=1);

namespace Fop\OpentechcalendarCoUk\Factory;

use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Fop\Geolocation\Geolocator;
use Location\Coordinate;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

final class OpentechcalendarCoUkMeetupFactory
{
    /**
     * @var Geolocator
     */
    private $geolocator;

    public function __construct(Geolocator $geolocator)
    {
        $this->geolocator = $geolocator;
    }

    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): ?Meetup
    {
        // skip meetup.com events - already covered by meetup.com package
        if (Strings::match($data['url'], '#https:\/\/(www\.)?meetup.com#')) {
            return null;
        }

        // only active normal meetup
        if ($data['deleted'] || (isset($data['canceled']) && $data['canceled']) || $data['is_physical'] === false) {
            return null;
        }

        $name = $data['summary'];
        $start = DateTime::from($data['start']['rfc2882timezone']);
        $group = $data['summary'];
        $location = $this->createLocation($data);

        if ($location === null) {
            return null;
        }

        $link = $data['siteurl'];

        return new Meetup($name, $group, $start, $location, $link);
    }

    /**
     * @param mixed[] $data
     */
    private function createLocation(array $data): ?Location
    {
        if (! isset($data['venue'])) {
            // fallback to city
            $city = $data['areas'][0]['title'];
            return $this->geolocator->createLocationFromCity($city);
        }

        $coordinate = new Coordinate((float) $data['venue']['lat'], (float) $data['venue']['lng']);

        return new Location($data['areas'][0]['title'], $data['country']['title'], $coordinate);
    }
}
