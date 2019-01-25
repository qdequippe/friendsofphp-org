<?php declare(strict_types=1);

namespace Fop\MeetupCom\Meetup;

use DateTimeInterface;
use DateTimeZone;
use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Fop\Geolocation\Geolocator;
use Location\Coordinate;
use Nette\Utils\DateTime;

final class MeetupComMeetupFactory
{
    /**
     * @var string[]
     */
    private $cityNormalizationMap = [
        'Praha-Nové Město' => 'Prague',
        'Brno-Královo Pole' => 'Brno',
        'Brno-střed-Veveří' => 'Brno',
        'Hlavní město Praha' => 'Prague',
        '1065 Budapest' => 'Budapest',
        'ISTANBUL' => 'Istanbul',
        'Wien' => 'Vienna',
        '1190 Wien' => 'Vienna',
        '8000 Aarhus C' => 'Aarhus',
        'Le Kremlin-Bicêtre' => 'Paris',
        'Parramatta' => 'Paris',
        'Stellenbosch' => 'Cape Town',
        '台北' => 'Taipei',
        'New Taipei City' => 'Taipei',
        # Japan
        '東京都' => 'Tokyo',
        '愛知県' => 'Aichi Prefecture',
        '兵庫県' => 'Hyōgo',
        # Germany
        'Köln' => 'Cologne',
        '10997 Berlin' => 'Berlin',
        '22765 Hamburg' => 'Hamburg',
        '76227 Karlsruhe' => 'Karlsruhe',
        'Unterföhrin' => 'Munich',
        # UK
        'EC2A 2BA' => 'London',
        'London, EC2Y 9AE' => 'London',
        'Oxford OX1 3BY' => 'Oxford',
        'Oxford OX2 6AE' => 'Oxford',
        'Reading RG1 1DG' => 'Reading',
        'M4 2AH' => 'Manchester',
        'BH12 1AZ' => 'Poole',
        'LE2 7DR' => 'Leicester',
        'BS2 0BY' => 'Bristol',
    ];

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

        $venue['city'] = $this->normalizeCity($venue['city']);

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

    private function normalizeCity(string $city): string
    {
        return $this->cityNormalizationMap[$city] ?? $city;
    }
}
