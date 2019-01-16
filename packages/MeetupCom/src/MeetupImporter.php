<?php declare(strict_types=1);

namespace Fop\MeetupCom;

use DateTimeInterface;
use DateTimeZone;
use Fop\Country\CountryResolver;
use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Fop\MeetupCom\Api\MeetupComApi;
use Location\Coordinate;
use Nette\Utils\DateTime;

final class MeetupImporter
{
    /**
     * @var string[]
     */
    private $groupsHavingMeetup = [];

    /**
     * @var string[]
     */
    private $cityNormalizationMap = [
        'Hlavní město Praha' => 'Prague',
        '1065 Budapest' => 'Budapest',
        'ISTANBUL' => 'Istanbul',
        'Wien' => 'Vienna',
        '8000 Aarhus C' => 'Aarhus',
        # Germany
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
     * @var MeetupComApi
     */
    private $meetupComApi;

    /**
     * @var DateTimeInterface
     */
    private $maxForecastDateTime;

    /**
     * @var CountryResolver
     */
    private $countryResolver;

    public function __construct(int $maxForecastDays, MeetupComApi $meetupComApi, CountryResolver $countryResolver)
    {
        $this->maxForecastDateTime = DateTime::from('+' . $maxForecastDays . 'days');
        $this->meetupComApi = $meetupComApi;
        $this->countryResolver = $countryResolver;
    }

    /**
     * @param int[] $groupIds
     * @return Meetup[]
     */
    public function importForGroupIds(array $groupIds): array
    {
        $meetups = [];
        $this->groupsHavingMeetup = [];

        $groupIdsChunks = array_chunk($groupIds, 200);

        foreach ($groupIdsChunks as $groupIdsChunk) {
            foreach ($this->meetupComApi->getMeetupsByGroupsIds($groupIdsChunk) as $meetup) {
                $startDateTime = $this->createStartDateTimeFromEventData($meetup);

                if ($this->shouldSkipMeetup($startDateTime, $meetup)) {
                    continue;
                }

                $meetups[] = $this->createMeetupFromEventData($meetup, $startDateTime);
            }
        }

        return $this->sortByStartDateTime($meetups);
    }

    /**
     * @param mixed[] $meetup
     */
    private function createStartDateTimeFromEventData(array $meetup): DateTimeInterface
    {
        // not sure why it adds extra "000" in the end
        $time = $this->normalizeTimestamp($meetup['time']);
        $utcOffset = $this->normalizeTimestamp($meetup['utc_offset']);

        return $this->createUtcDateTime($time, $utcOffset);
    }

    /**
     * @param mixed[] $meetup
     */
    private function shouldSkipMeetup(DateTimeInterface $startDateTime, array $meetup): bool
    {
        // not announced yet
        if (isset($meetup['announced']) && $meetup['announced'] === false) {
            return true;
        }

        // skip past meetups
        if ($meetup['status'] !== 'upcoming') {
            return true;
        }

        // skip meetups too far in the future
        if ($startDateTime > $this->maxForecastDateTime) {
            return true;
        }

        // draft event, not ready yet
        if (! isset($meetup['venue'])) {
            return true;
        }

        $groupName = $meetup['group']['name'];

        // keep only 1 nearest meetup for the group - keep it present and less crowded
        if (in_array($groupName, $this->groupsHavingMeetup, true)) {
            return true;
        }

        $this->groupsHavingMeetup[] = $groupName;

        return false;
    }

    /**
     * @param mixed[] $event
     */
    private function createMeetupFromEventData(array $event, DateTimeInterface $startDateTime): Meetup
    {
        $venue = $event['venue'];

        // base location of the meetup, use it for event location
        if ($venue['lon'] === 0 || $venue['lat'] === 0) {
            $venue['lon'] = $event['group']['group_lon'];
            $venue['lat'] = $event['group']['group_lat'];
        }

        $venue = $this->normalizeCityStates($venue);

        $venue['city'] = $this->normalizeCity($venue['city']);
        $country = $this->countryResolver->resolveByVenue($venue);

        $coordinate = new Coordinate($venue['lat'], $venue['lon']);
        $location = new Location($venue['city'], $country, $coordinate);

        $event['name'] = trim($event['name']);
        $event['name'] = str_replace('@', '', $event['name']);

        return new Meetup($event['name'], $event['group']['name'], $startDateTime, $location, $event['event_url']);
    }

    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    private function sortByStartDateTime(array $meetups): array
    {
        usort($meetups, function (Meetup $firstMeetup, Meetup $secondMeetup): int {
            return $firstMeetup->getStartDateTime() <=> $secondMeetup->getStartDateTime();
        });

        return $meetups;
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
