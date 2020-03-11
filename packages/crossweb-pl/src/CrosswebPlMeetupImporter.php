<?php declare(strict_types=1);

namespace Fop\CrosswebPl;

use Fop\Core\ValueObject\Meetup;
use Fop\Core\Xml\XmlReader;
use Fop\CrosswebPl\Meetup\CrosswebPlMeetupFactory;
use Fop\Meetup\Contract\MeetupImporterInterface;
use Nette\Utils\Strings;

final class CrosswebPlMeetupImporter implements MeetupImporterInterface
{
    /**
     * @var string
     */
    private const XML_CALENDAR_FEED = 'https://crossweb.pl/feed/wydarzenia/php/';

    /**
     * @var XmlReader
     */
    private $xmlReader;

    /**
     * @var CrosswebPlMeetupFactory
     */
    private $crosswebPlMeetupFactory;

    public function __construct(XmlReader $xmlReader, CrosswebPlMeetupFactory $crosswebPlMeetupFactory)
    {
        $this->xmlReader = $xmlReader;
        $this->crosswebPlMeetupFactory = $crosswebPlMeetupFactory;
    }

    public function getKey(): string
    {
        return 'crossweb-pl';
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $xml = $this->xmlReader->loadFile(self::XML_CALENDAR_FEED);

        $meetups = [];

        foreach ($xml->channel->item as $meetup) {
            $name = (string) $meetup->title;
            $url = $this->clearUrl((string) $meetup->link);

            $meetup = $this->crosswebPlMeetupFactory->createMeetupFromMeetupUrl($url, $name);
            if ($meetup === null) {
                continue;
            }

            $meetups[] = $meetup;
        }

        return $meetups;
    }

    /**
     * clear "utm_source", not needed
     */
    private function clearUrl(string $url): string
    {
        return Strings::replace($url, '#(https.*?)\?utm.*?$#', '$1');
    }
}
