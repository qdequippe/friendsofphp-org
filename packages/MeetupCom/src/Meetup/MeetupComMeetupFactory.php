<?php declare(strict_types=1);

namespace Fop\MeetupCom\Meetup;

use DateTimeInterface;
use DateTimeZone;
use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Fop\Geolocation\Geolocator;
use Fop\Utils\CityNormalizer;
use Location\Coordinate;
use Nette\Utils\DateTime;

final class MeetupComMeetupFactory
{
    /**
     * @var Geolocator
     */
    private $geolocator;

    /**
     * @var CityNormalizer
     */
    private $cityNormalizer;

    public function __construct(Geolocator $geolocator, CityNormalizer $cityNormalizer)
    {
        $this->geolocator = $geolocator;
        $this->cityNormalizer = $cityNormalizer;
    }

    /**
     * @param mixed[] $data
     */
    public function createFromData(array $data): ?Meetup
    {
        if ($this->shouldSkipMeetup($data)) {
            return null;
        }

        $startDateTime = $this->createStartDateTimeFromEventData($data);
        $location = $this->createLocation($data);

        $name = $this->createName($data);

        return new Meetup($name, $data['group']['name'], $startDateTime, $location, $data['event_url']);
    }

    /**
     * @param mixed[] $meetup
     */
    private function shouldSkipMeetup(array $meetup): bool
    {
        // not announced yet
        if (isset($meetup['announced']) && $meetup['announced'] === false) {
            return true;
        }

        // skip past meetups
        if ($meetup['status'] !== 'upcoming') {
            return true;
        }

        // draft event, not ready yet
        if (! isset($meetup['venue'])) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed[] $data
     */
    private function createStartDateTimeFromEventData(array $data): DateTimeInterface
    {
        // not sure why it adds extra "000" in the end
        $time = $this->normalizeTimestamp($data['time']);
        $utcOffset = $this->normalizeTimestamp($data['utc_offset']);

        return $this->createUtcDateTime($time, $utcOffset);
    }

    /**
     * @param mixed[] $data
     */
    private function createLocation(array $data): Location
    {
        $venue = $data['venue'];

        // base location of the meetup, use it for event location
        if ($venue['lon'] === 0 || $venue['lat'] === 0 || (isset($venue['city']) && $venue['city'] === 'Shenzhen')) {
            // correction for Shenzhen miss-location to America
            $venue['lon'] = $data['group']['group_lon'];
            $venue['lat'] = $data['group']['group_lat'];
        }

        $venue = $this->normalizeCityStates($venue);
        if (! isset($venue['city'])) {
            $country = $this->geolocator->getCountryJsonByLatitudeAndLongitude($venue['lat'], $venue['lon']);
            $venue['city'] = $country['address']['city'];
        }

        $venue['city'] = $this->cityNormalizer->normalize($venue['city']);

        $country = $this->geolocator->resolveCountryByVenue($venue);

        $coordinate = new Coordinate($venue['lat'], $venue['lon']);

        return new Location($venue['city'], $country, $coordinate);
    }

    /**
     * @param mixed[] $data
     */
    private function createName(array $data): string
    {
        $name = trim($data['name']);

        return str_replace('@', '', $name);
    }

    private function normalizeTimestamp(int $timestamp): int
    {
        return (int) substr((string) $timestamp, 0, -3);
    }

    private function createUtcDateTime(int $time, int $utcOffset): DateTime
    {
        return DateTime::from($time + $utcOffset)
            ->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * @param mixed[] $venue
     * @return mixed[]
     */
    private function normalizeCityStates(array $venue): array
    {
        if (isset($venue['city']) && $venue['city'] !== null) {
            return $venue;
        }

        if ($venue['localized_country_name'] === 'Singapore') {
            $venue['city'] = 'Singapore';
        }

        return $venue;
    }
}
