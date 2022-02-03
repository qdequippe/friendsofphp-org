<?php

declare(strict_types=1);

namespace Fop\OpentechcalendarCoUk;

use Fop\Meetup\Contract\MeetupImporterInterface;
use Fop\Meetup\ValueObject\Meetup;
use Fop\OpentechcalendarCoUk\Factory\OpentechcalendarCoUkMeetupFactory;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symplify\SmartFileSystem\SmartFileSystem;

final class OpentechcalendarCoUkMeetupImporter implements MeetupImporterInterface
{
    /**
     * @var string
     */
    private const EVENTS_JSON_URL = 'https://opentechcalendar.co.uk/api1/events.json';

    public function __construct(
        private readonly SmartFileSystem $smartFileSystem,
        private readonly OpentechcalendarCoUkMeetupFactory $opentechcalendarCoUkMeetupFactory
    ) {
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $jsonContent = $this->smartFileSystem->readFile(self::EVENTS_JSON_URL);
        $json = Json::decode($jsonContent, Json::FORCE_ARRAY);

        $eventsJson = $json['data'] ?? [];

        $meetups = [];
        foreach ($eventsJson as $eventJson) {
            if (! Strings::match($eventJson['summary'], '#\b(php|wordpress)\b#i')) {
                continue;
            }

            $meetup = $this->opentechcalendarCoUkMeetupFactory->createFromArray($eventJson);
            if (! $meetup instanceof Meetup) {
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
