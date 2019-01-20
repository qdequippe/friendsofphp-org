<?php declare(strict_types=1);

namespace Fop\OpentechcalendarCoUk;

use Fop\Contract\MeetupImporterInterface;
use Fop\Entity\Meetup;
use Fop\OpentechcalendarCoUk\Factory\MeetupFactory;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;

final class OpentechcalendarCoUkMeetupImporter implements MeetupImporterInterface
{
    /**
     * @var string
     */
    private const EVENTS_JSON_URL = 'https://opentechcalendar.co.uk/api1/events.json';

    /**
     * @var MeetupFactory
     */
    private $meetupFactory;

    public function __construct(MeetupFactory $meetupFactory)
    {
        $this->meetupFactory = $meetupFactory;
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $jsonContent = FileSystem::read(self::EVENTS_JSON_URL);
        $json = Json::decode($jsonContent, Json::FORCE_ARRAY);

        $eventsJson = $json['data'] ?? [];

        $meetups = [];
        foreach ($eventsJson as $eventJson) {
            if (! Strings::match($eventJson['summary'], '#\b(php|wordpress)\b#i')) {
                continue;
            }

            $meetup = $this->meetupFactory->createFromArray($eventJson);
            if ($meetup === null) {
                continue;
            }

            $meetups[] = $meetup;
        }

        return $meetups;
    }

    public function getKey(): string
    {
        return 'opentechcalendar-co-uk';
    }
}
