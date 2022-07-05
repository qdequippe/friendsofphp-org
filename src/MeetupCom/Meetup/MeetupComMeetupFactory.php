<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Meetup;

use DateTimeInterface;
use Fop\Exception\ShouldNotHappenException;
use Fop\Geolocation\Geolocator;
use Fop\Meetup\ValueObject\Location;
use Fop\Meetup\ValueObject\Meetup;
use Fop\Utils\CityNormalizer;
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

    /**
     * @var string
     */
    private const NAME = 'name';

    /**
     * @var string
     */
    private const VENUE = 'venue';

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

        $utcStartDateTime = $this->createUtcStartDateTime($data);

        $location = $this->createLocation($data);
        $name = $this->createName($data);
        $isOnline = (bool) $data[self::IS_ONLINE_EVENT];

        return new Meetup(
            $name,
            $data[self::GROUP][self::NAME],
            $utcStartDateTime,
            $data['local_date'],
            $data['local_time'],
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

        // special venue, not really a meetup, but a promo - see https://www.meetup.com/bostonphp/events/283821265/
        if (isset($meetup[self::VENUE][self::NAME])) {
            return $meetup[self::VENUE][self::NAME] === 'Virtual';
        }

        return false;
    }

    /**
     * @param mixed[] $data
     */
    private function createUtcStartDateTime(array $data): DateTime
    {
        // not sure why it adds extra "000" in the end
        $unixTimestamp = (int) substr((string) $data['time'], 0, -3);

        $dateTime = DateTime::createFromFormat('U', (string) $unixTimestamp);
        if (! $dateTime instanceof DateTimeInterface) {
            throw new ShouldNotHappenException();
        }

        return $dateTime;
    }

    /**
     * @param mixed[] $data
     */
    private function createLocation(array $data): Location
    {
        if (isset($data[self::VENUE])) {
            $venue = $data[self::VENUE];
        } else {
            // no specific venue defined
            $localizedLocation = $data['group']['localized_location'];
            [$city, $country] = explode(', ', $localizedLocation);

            $coordinate = $this->geolocator->resolveLatLonByCityAndCountry($localizedLocation);
            return new Location($city, $country, $coordinate);
        }

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
        $name = trim($data[self::NAME]);
        return str_replace('@', '', $name);
    }

    /**
     * @param array<string, mixed> $venue
     * @return array<string, mixed>
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
