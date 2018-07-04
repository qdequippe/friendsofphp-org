<?php declare(strict_types=1);

namespace Fop\Importer;

use DateTimeInterface;
use Fop\Location;
use Fop\Meetup;
use GuzzleHttp\Client;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use function GuzzleHttp\Psr7\build_query;

final class MeetupsFromMeetupComImporter
{
    /**
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/2/events';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var DateTimeInterface
     */
    private $nowDateTime;

    /**
     * @var string
     */
    private $meetupApiKey;

    public function __construct(Client $client, string $meetupApiKey)
    {
        $this->client = $client;
        $this->nowDateTime = DateTime::from('now');
        $this->meetupApiKey = $meetupApiKey;
    }

    /**
     * @return Meetup[]
     */
    public function importForGroupIds(string $groupIds): array
    {
        $query = self::API_EVENTS_BY_GROUPS_URL . '?' . build_query([
            # https://www.meetup.com/meetup_api/docs/2/events/#params
            'group_id' => $groupIds,
            # https://www.meetup.com/meetup_api/auth/#keys
            'key' => $this->meetupApiKey,
            // no_earlier_than "today" - @todo
        ]);

        $response = $this->client->request('GET', $query);

        $result = Json::decode($response->getBody(), Json::FORCE_ARRAY);
        $events = $result['results'];

        $meetups = [];
        foreach ($events as $event) {
            // not sure why, but probably some bug
            $time = substr((string) $event['time'], 0, -3);
            $startDateTime = DateTime::from($time);

            if ($this->shouldSkipMeetup($startDateTime, $event)) {
                continue;
            }

            $meetups[] = $this->createMeetupFromEventData($event, $startDateTime);
        }

        return $meetups;
    }

    /**
     * @param mixed[] $event
     */
    private function shouldSkipMeetup(DateTimeInterface $startDateTime, array $event): bool
    {
        // skip past meetups
        if ($startDateTime < $this->nowDateTime) {
            return true;
        }

        // draft event, not ready yet
        return ! isset($event['venue']);
    }

    /**
     * @param mixed[] $event
     */
    private function createMeetupFromEventData(array $event, DateTimeInterface $startDateTime): Meetup
    {
        $venue = $event['venue'];

        $location = new Location($venue['city'], $venue['localized_country_name'], $venue['lon'], $venue['lat']);

        return new Meetup($event['name'], $event['group']['name'], $startDateTime, $location);
    }
}
