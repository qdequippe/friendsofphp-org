<?php declare(strict_types=1);

namespace Fop\Importer;

use DateTimeInterface;
use Fop\Location;
use Fop\Meetup;
use GuzzleHttp\Client;
use Nette\Utils\DateTime;
use Nette\Utils\Json;

final class MeetupsFromMeetupComImporter
{
    /**
     * @var string
     */
    private const URL_API = 'http://api.meetup.com/2/events?group_urlname=%s';

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
    public function importForGroupName(string $groupName): array
    {
        $url = sprintf(self::URL_API, $groupName);

        $response = $this->client->request('GET', $url, [
            # see https://www.meetup.com/meetup_api/auth/#keys
            'key' => $this->meetupApiKey,
        ]);

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
