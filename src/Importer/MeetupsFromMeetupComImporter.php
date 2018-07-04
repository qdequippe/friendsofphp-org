<?php declare(strict_types=1);

namespace Fop\Importer;

use DateTimeInterface;
use Fop\Api\MeetupComApi;
use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Nette\Utils\DateTime;

final class MeetupsFromMeetupComImporter
{
    /**
     * @var DateTimeInterface
     */
    private $nowDateTime;

    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    public function __construct(string $meetupComApiKey, MeetupComApi $meetupComApi)
    {
        $this->nowDateTime = DateTime::from('now');
        $this->meetupComApi = $meetupComApi;
    }

    /**
     * @param int[] $groupIds
     * @return Meetup[]
     */
    public function importForGroupIds(array $groupIds): array
    {
        $meetups = [];
        foreach ($this->meetupComApi->getMeetupsByGroupsIds($groupIds) as $meetup) {
            // not sure why, but probably some bug
            $time = substr((string) $meetup['time'], 0, -3);
            $startDateTime = DateTime::from($time);

            if ($this->shouldSkipMeetup($startDateTime, $meetup)) {
                continue;
            }

            $meetups[] = $this->createMeetupFromEventData($meetup, $startDateTime);
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
