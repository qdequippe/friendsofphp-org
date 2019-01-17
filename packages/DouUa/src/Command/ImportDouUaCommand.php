<?php declare(strict_types=1);

namespace Fop\DouUa\Command;

use DateTimeInterface;
use Fop\DouUa\Xml\XmlReader;
use Fop\Entity\Location;
use Fop\Entity\Meetup;
use Fop\Location\LocationResolver;
use Fop\MeetupCom\Crawler\CrawlerFactory;
use Fop\Repository\MeetupRepository;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ImportDouUaCommand extends Command
{
    /**
     * @var string
     */
    private const XML_CALENDAR_FEED = 'https://dou.ua/calendar/feed/PHP/';

    /**
     * @var int
     */
    private $maxForecastDays;

    /**
     * @var CrawlerFactory
     */
    private $crawlerFactory;

    /**
     * @var LocationResolver
     */
    private $locationResolver;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var DateTimeInterface
     */
    private $maxForecastDateTime;

    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    /**
     * @var XmlReader
     */
    private $xmlReader;

    public function __construct(
        CrawlerFactory $crawlerFactory,
        LocationResolver $locationResolver,
        SymfonyStyle $symfonyStyle,
        int $maxForecastDays,
        MeetupRepository $meetupRepository,
        XmlReader $xmlReader
    ) {
        parent::__construct();
        $this->crawlerFactory = $crawlerFactory;
        $this->locationResolver = $locationResolver;
        $this->symfonyStyle = $symfonyStyle;
        $this->maxForecastDays = $maxForecastDays;
        $this->maxForecastDateTime = DateTime::from('+' . $maxForecastDays . 'days');

        $this->meetupRepository = $meetupRepository;
        $this->xmlReader = $xmlReader;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Imports events from https://dou.ua/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $xml = $this->xmlReader->loadFile(self::XML_CALENDAR_FEED);

        $meetups = [];
        foreach ($xml->channel->item as $meetup) {
            // name
            $name = (string) $meetup->title;

            // meetup link
            $url = (string) $meetup->link;

            $crawler = $this->crawlerFactory->createFromUrl($url);
            if ($crawler === null) {
                continue;
            }

            $json = $this->resolveJsonData($crawler);
            if ($json === null) {
                continue;
            }

            // to be sure
            $json['name'] = html_entity_decode($json['name']);

            // group
            $group = $this->resolveGroupName($name);

            // location
            $location = $this->resolveLocation($json);
            if ($location === null) {
                continue;
            }

            $startDateTime = $this->resolveStartDateTime($crawler->text(), $json);
            if ($startDateTime === null) {
                continue;
            }

            // skip meetups too far in the future
            if ($startDateTime > $this->maxForecastDateTime) {
                continue;
            }

            $meetups[] = new Meetup($name, $group, $startDateTime, $location, $url);
        }

        $this->symfonyStyle->note(
            sprintf('Loaded %d meetups for next %d days', count($meetups), $this->maxForecastDays)
        );

        $this->meetupRepository->saveImportsToFile($meetups, 'dou-ua');
        $this->symfonyStyle->success('Done');

        return ShellCode::SUCCESS;
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

    private function resolveGroupName(string $name): string
    {
        $match = Strings::match($name, '#^(?<group>.*?)\s+(\#|\d)#');

        return $match['group'] ?? $name;
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

        return $this->locationResolver->createFromCity($city);
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
}
