<?php declare(strict_types=1);

namespace Fop\DouUa;

use Fop\Contract\MeetupImporterInterface;
use Fop\DouUa\Meetup\DouUaMeetupFactory;
use Fop\DouUa\Xml\XmlReader;
use Fop\Entity\Meetup;

final class DouUaMeetupImporter implements MeetupImporterInterface
{
    /**
     * @var string
     */
    private const XML_CALENDAR_FEED = 'https://dou.ua/calendar/feed/PHP/';

    /**
     * @var XmlReader
     */
    private $xmlReader;

    /**
     * @var DouUaMeetupFactory
     */
    private $douUaMeetupFactory;

    public function __construct(XmlReader $xmlReader, DouUaMeetupFactory $douUaMeetupFactory)
    {
        $this->xmlReader = $xmlReader;
        $this->douUaMeetupFactory = $douUaMeetupFactory;
    }

    public function getKey(): string
    {
        return 'dou-ua';
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $xml = $this->xmlReader->loadFile(self::XML_CALENDAR_FEED);

        $meetups = [];
        foreach ($xml->channel->item as $meetup) {
            // name
            $name = (string) $meetup->title;

            // meetup link
            $url = (string) $meetup->link;

            $meetup = $this->douUaMeetupFactory->createMeetupFromUrlAndName($url, $name);
            if ($meetup === null) {
                continue;
            }

            $meetups[] = $meetup;
        }

        return $meetups;
    }
}
