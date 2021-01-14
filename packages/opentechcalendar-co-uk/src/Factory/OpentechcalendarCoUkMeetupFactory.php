<?php declare(strict_types=1);

namespace Fop\OpentechcalendarCoUk\Factory;

use Fop\Core\Exception\ShouldNotHappenException;
use Fop\Core\Geolocation\Geolocator;
use Fop\Meetup\ValueObject\Location;
use Fop\Meetup\ValueObject\Meetup;
use Location\Coordinate;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

final class OpentechcalendarCoUkMeetupFactory
{
    public function __construct(
        private Geolocator $geolocator
    ) {
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

        if ($location->getCoordinateLongitude() === 0.0 && $location->getCoordinateLatitude() === 0.0) {
            throw new ShouldNotHappenException(sprintf('Invalid location resolved for "%s".', $name));
        }

        $link = $data['siteurl'];

        return new Meetup($name, $group, $start, $location, $link);
    }

    /**
     * @param mixed[] $data
     */
    private function createLocation(array $data): ?Location
    {
        if ($this->isVenueMissing($data)) {
            // fallback to city
            $city = $data['areas'][0]['title'];
            return $this->geolocator->createLocationFromCity($city);
        }

        $coordinate = new Coordinate((float) $data['venue']['lat'], (float) $data['venue']['lng']);

        return new Location($data['areas'][0]['title'], $data['country']['title'], $coordinate);
    }

    private function isVenueMissing(array $data): bool
    {
        if (! isset($data['venue'])) {
            return true;
        }

        if ($data['venue']['lat'] === null || $data['venue']['lat']) {
            return true;
        }

        return false;
    }
}
