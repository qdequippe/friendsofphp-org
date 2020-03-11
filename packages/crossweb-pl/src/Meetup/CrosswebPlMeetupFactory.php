<?php declare(strict_types=1);

namespace Fop\CrosswebPl\Meetup;

use DateTimeInterface;
use Fop\Core\Geolocation\Geolocator;
use Fop\Core\ValueObject\Location;
use Fop\Core\ValueObject\Meetup;
use Fop\Meetup\Meetup\MeetupFactory;
use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

final class CrosswebPlMeetupFactory
{
    /**
     * @var Geolocator
     */
    private $geolocator;

    /**
     * @var MeetupFactory
     */
    private $meetupFactory;

    public function __construct(Geolocator $geolocator, MeetupFactory $meetupFactory)
    {
        $this->geolocator = $geolocator;
        $this->meetupFactory = $meetupFactory;
    }

    public function createMeetupFromMeetupUrl(string $url, string $name): ?Meetup
    {
        $urlContent = FileSystem::read($url);

        $location = $this->resolveLocation($urlContent);
        if ($location === null) {
            return null;
        }

        $startDateTime = $this->resolveStartDateTime($urlContent);
        if ($startDateTime === null) {
            return null;
        }

        return $this->meetupFactory->create($name, $this->resolveGroupName($name), $startDateTime, $location, $url);
    }

    private function resolveLocation(string $content): ?Location
    {
        $match = Strings::match($content, '#Miasto:(.*?)wydarzenia(.*?)>(?<city>.*?)<#sm');

        /** @var string|null $city */
        $city = $match['city'] ?? null;
        if ($city === null) {
            return null;
        }

        return $this->geolocator->createLocationFromCity($city);
    }

    private function resolveStartDateTime(string $content): ?DateTimeInterface
    {
        $match = Strings::match($content, '#Godzina.*?(?<time>\d+\:\d+)#s');
        $time = $match['time'] ?? '19:00'; // fallback to prevent 00:00

        $match = Strings::match($content, '#Data:.*?(?<date>\d+\.\d+\.\d+)#s');
        $date = $match['date'] ?? null;

        if ($date === null) {
            return null;
        }

        return DateTime::from($date . ' ' . $time);
    }

    private function resolveGroupName(string $name): string
    {
        // basically remove everything after "#", that signal number of meetup
        $match = Strings::match($name, '#^(?<group>.*?)\s+(\#|\d)#');

        return $match['group'] ?? $name;
    }
}
