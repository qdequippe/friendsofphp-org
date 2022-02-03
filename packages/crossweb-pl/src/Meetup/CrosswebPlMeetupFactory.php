<?php

declare(strict_types=1);

namespace Fop\CrosswebPl\Meetup;

use DateTimeInterface;
use Fop\Core\Geolocation\Geolocator;
use Fop\Meetup\ValueObject\Location;
use Fop\Meetup\ValueObject\Meetup;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Symplify\SmartFileSystem\SmartFileSystem;

final class CrosswebPlMeetupFactory
{
    public function __construct(
        private readonly Geolocator $geolocator,
        private readonly SmartFileSystem $smartFileSystem
    ) {
    }

    public function createMeetupFromMeetupUrl(string $url, string $name): ?Meetup
    {
        $urlContent = $this->smartFileSystem->readFile($url);

        $location = $this->resolveLocation($urlContent);
        if (! $location instanceof Location) {
            return null;
        }

        $startDateTime = $this->resolveStartDateTime($urlContent);
        if (! $startDateTime instanceof \DateTimeInterface) {
            return null;
        }

        $groupName = $this->resolveGroupName($name);

        return new Meetup(
            $name,
            $groupName,
            $startDateTime,
            $url,
            $location->getCity(),
            $location->getCountry(),
            $location->getCoordinateLatitude(),
            $location->getCoordinateLongitude()
        );
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
        // fallback to prevent 00:00
        $time = $match['time'] ?? '19:00';

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
