<?php declare(strict_types=1);

namespace Fop\DouUa\Meetup;

use DateTimeInterface;
use Fop\DouUa\Crawler\CrawlerFactory;
use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Fop\Geolocation\Geolocator;
use Fop\Meetup\MeetupFactory;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Symfony\Component\DomCrawler\Crawler;

final class DouUaMeetupFactory
{
    /**
     * @var CrawlerFactory
     */
    private $crawlerFactory;

    /**
     * @var Geolocator
     */
    private $geolocator;

    /**
     * @var MeetupFactory
     */
    private $meetupFactory;

    public function __construct(CrawlerFactory $crawlerFactory, Geolocator $geolocator, MeetupFactory $meetupFactory)
    {
        $this->crawlerFactory = $crawlerFactory;
        $this->geolocator = $geolocator;
        $this->meetupFactory = $meetupFactory;
    }

    public function createMeetupFromUrlAndName(string $url, string $name): ?Meetup
    {
        $crawler = $this->crawlerFactory->createFromUrl($url);
        if ($crawler === null) {
            return null;
        }

        $json = $this->resolveJsonData($crawler);
        if ($json === null) {
            return null;
        }

        // to be sure
        $json['name'] = html_entity_decode($json['name']);

        $location = $this->resolveLocation($json);
        if ($location === null) {
            return null;
        }

        $startDateTime = $this->resolveStartDateTime($crawler->text(), $json);
        if ($startDateTime === null) {
            return null;
        }

        return $this->meetupFactory->create($name, $this->resolveGroupName($name), $startDateTime, $location, $url);
    }

    /**
     * @return mixed[]|null
     */
    private function resolveJsonData(Crawler $crawler): ?array
    {
        $jsonData = $crawler->filterXPath('//script[@type="application/ld+json"]/text()');
        if ($jsonData->getNode(0) === null) { // has some result?
            return null;
        }

        try {
            return Json::decode($jsonData->text(), Json::FORCE_ARRAY);
        } catch (JsonException $jsonException) {
            return null;
        }
    }

    /**
     * @param mixed[] $json
     */
    private function resolveLocation(array $json): ?Location
    {
        /** @var string|null $city */
        $city = $json['location']['address']['addressLocality'] ?? null;
        if ($city === null) {
            return null;
        }

        $city = html_entity_decode($city);

        return $this->geolocator->createLocationFromCity($city);
    }

    /**
     * @param mixed[] $json
     */
    private function resolveStartDateTime(string $pageContent, array $json): ?DateTimeInterface
    {
        $date = html_entity_decode($json['startDate']);
        $pageContent = html_entity_decode($pageContent);

        $match = Strings::match($pageContent, '#(?<time>\d+:\d+)\s+—\s+\d+:\d+#');
        $time = $match['time'] ?? '19:00'; // assumption to preven times like 00:00 - @todo check in template instead

        return DateTime::from($date . ' ' . $time);
    }

    private function resolveGroupName(string $name): string
    {
        $match = Strings::match($name, '#^(?<group>.*?)\s+(\#|\d)#');

        return $match['group'] ?? $name;
    }
}
