<?php declare(strict_types=1);

namespace Fop\PosobotaCz;

use Fop\Contract\MeetupImporterInterface;
use Fop\Entity\Meetup;
use Fop\Geolocation\Geolocator;
use Fop\Utils\CityNormalizer;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

final class PosobotaCzMeetupImporter implements MeetupImporterInterface
{
    /**
     * @var string
     */
    private const LAST_EVENT_CALENDAR = 'https://www.posobota.cz/feed.ical.php';

    /**
     * @var Geolocator
     */
    private $geolocator;

    /**
     * @var IcalParser
     */
    private $icalParser;

    /**
     * @var CityNormalizer
     */
    private $cityNormalizer;

    public function __construct(Geolocator $geolocator, IcalParser $icalParser, CityNormalizer $cityNormalizer)
    {
        $this->geolocator = $geolocator;
        $this->icalParser = $icalParser;
        $this->cityNormalizer = $cityNormalizer;
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $data = $this->icalParser->parseIcalUrlToArray(self::LAST_EVENT_CALENDAR);

        $name = $this->resolveName($data['UID']);
        $date = DateTime::from($data['DTSTAMP']);

        $city = $this->cityNormalizer->normalize($data['LOCATION']);
        $location = $this->geolocator->createLocationFromCity($city);
        if ($location === null) {
            return [];
        }

        $url = $data['URL'];

        $meetup = new Meetup($name, 'Posobota', $date, $location, $url);

        return [$meetup];
    }

    public function getKey(): string
    {
        return 'posobota-cz';
    }

    private function resolveName(string $uid): string
    {
        $match = Strings::match($uid, '#(?<id>.*?)@#');
        $id = $match['id'] ?? '';

        return 'Poslední Sobota ' . $id;
    }
}
