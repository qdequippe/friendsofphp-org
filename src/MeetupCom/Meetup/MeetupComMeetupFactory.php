<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Meetup;

use Fop\Meetup\ValueObject\Location;
use Fop\Meetup\ValueObject\Meetup;
use Fop\Utils\CityNormalizer;
use Location\Coordinate;

final class MeetupComMeetupFactory
{
    /**
     * @var string
     */
    private const GROUP = 'organizer';

    /**
     * @var string
     */
    private const CITY = 'city';

    /**
     * @var string
     */
    private const NAME = 'name';

    public function __construct(
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

        $location = $this->createLocation($data);
        $name = $this->createName($data);

        $localStartDate = new \DateTimeImmutable($data['startDate']);

        return new Meetup(
            $name,
            $data[self::GROUP][self::NAME],
            $localStartDate->setTimezone(new \DateTimeZone('UTC')),
            $localStartDate->format('Y-m-d'),
            $localStartDate->format('H:i'),
            $data['url'],
            $location->getCity(),
            $location->getCountry(),
            $location->getCoordinateLatitude(),
            $location->getCoordinateLongitude(),
        );
    }

    /**
     * @param mixed[] $meetup
     */
    private function shouldSkipMeetup(array $meetup): bool
    {
        // skip online events, focus on meeting people in person again
        if (isset($meetup['eventAttendanceMode']) && str_contains(
            $meetup['eventAttendanceMode'],
            'OnlineEventAttendanceMode'
        )) {
            return true;
        }

        // no location with address
        if (isset($meetup['location']['address']) === false) {
            return true;
        }

        // special venue, not really a meetup, but a promo - see https://www.meetup.com/bostonphp/events/283821265/
        if (isset($meetup['location']['name']) && $meetup['location']['name'] === 'Virtual') {
            return true;
        }

        return false;
    }

    /**
     * @param mixed[] $data
     */
    private function createLocation(array $data): Location
    {
        $city = html_entity_decode($data['location']['address']['addressLocality']);
        $venue[self::CITY] = $this->cityNormalizer->normalize($city);

        $coordinate = new Coordinate($data['location']['geo']['latitude'], $data['location']['geo']['longitude']);

        $country = $data['location']['address']['addressCountry']['name'] ?? $data['location']['address']['addressCountry'];

        return new Location($venue[self::CITY], html_entity_decode($country), $coordinate);
    }

    /**
     * @param array{name: string} $data
     */
    private function createName(array $data): string
    {
        $name = html_entity_decode(trim($data[self::NAME]));

        return str_replace('@', '', $name);
    }
}
