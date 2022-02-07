<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Meetup;

use DateTimeZone;
use Fop\Core\Geolocation\Geolocator;
use Fop\Core\Utils\CityNormalizer;
use Fop\Meetup\ValueObject\Location;
use Fop\Meetup\ValueObject\Meetup;
use Location\Coordinate;
use Nette\Utils\DateTime;

final class MeetupComMeetupFactory
{
    /**
     * @var string
     */
    private const GROUP = 'group';

    /**
     * @var string
     */
    private const LON = 'lon';

    /**
     * @var string
     */
    private const LAT = 'lat';

    /**
     * @var string
     */
    private const CITY = 'city';

    /**
     * @var string
     */
    private const IS_ONLINE_EVENT = 'is_online_event';

    public function __construct(
        private readonly Geolocator $geolocator,
        private readonly CityNormalizer $cityNormalizer
    ) {
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
        $isOnline = (bool) $data[self::IS_ONLINE_EVENT];

        return new Meetup(
            $name,
            $data[self::GROUP]['name'],
            $startDateTime,
            $data['link'],
            $location->getCity(),
            $location->getCountry(),
            $location->getCoordinateLatitude(),
            $location->getCoordinateLongitude(),
            $isOnline
        );
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
        return ! isset($meetup['venue']);
    }

    /**
     * @param mixed[] $data
     */
    private function createStartDateTimeFromEventData(array $data): DateTime
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

        if (isset($data[self::IS_ONLINE_EVENT]) && $data[self::IS_ONLINE_EVENT] === true) {
            // online event probably
            $localizedLocation = $data['group']['localized_location'];
            [$city, $country] = explode(', ', (string) $localizedLocation);

            $coordinate = $this->geolocator->resolveLatLonByCityAndCountry($localizedLocation);
            return new Location($city, $country, $coordinate);
        }

        // base location of the meetup, use it for event location
        if (isset($venue[self::LON]) && $venue[self::LON] === 0 || $venue[self::LAT] === 0 || (isset($venue[self::CITY]) && $venue[self::CITY] === 'Shenzhen')) {
            // correction for Shenzhen miss-location to America
            $venue[self::LON] = $data[self::GROUP]['group_lon'];
            $venue[self::LAT] = $data[self::GROUP]['group_lat'];
        }

        $venue = $this->normalizeCityStates($venue);
        if (! isset($venue[self::CITY])) {
            $country = $this->geolocator->getCountryJsonByLatitudeAndLongitude($venue[self::LAT], $venue[self::LON]);
            $venue[self::CITY] = $country['address'][self::CITY];
        }

        $venue[self::CITY] = $this->cityNormalizer->normalize($venue[self::CITY]);

        $country = $this->geolocator->resolveCountryByVenue($venue);

        $coordinate = new Coordinate($venue[self::LAT], $venue[self::LON]);

        return new Location($venue[self::CITY], $country, $coordinate);
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
        $dateTime = DateTime::from($time + $utcOffset);
        return $dateTime->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * @param mixed[] $venue
     * @return mixed[]
     */
    private function normalizeCityStates(array $venue): array
    {
        if (isset($venue[self::CITY]) && $venue[self::CITY] !== null) {
            return $venue;
        }

        if ($venue['localized_country_name'] === 'Singapore') {
            $venue[self::CITY] = 'Singapore';
        }

        return $venue;
    }
}
