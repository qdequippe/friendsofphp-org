<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Entity\Group;
use Fop\MeetupCom\Filter\PhpRelatedFilter;
use Fop\Repository\GroupRepository;
use Fop\Utils\Arrays;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rinvex\Country\Country;
use Rinvex\Country\CountryLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class CrawlCommand extends Command
{
    /**
     * @var string[]
     */
    private $topicsToCrawl = [];

    /**
     * @var mixed[][]
     */
    private $groupsByCountry = [];

    /**
     * @var string[]
     */
    private $countryCodesWithNoPhpGroups = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var PhpRelatedFilter
     */
    private $phpRelatedFilter;

    /**
     * @param string[] $topicsToCrawl
     */
    public function __construct(
        SymfonyStyle $symfonyStyle,
        GroupRepository $groupRepository,
        PhpRelatedFilter $phpRelatedFilter,
        array $topicsToCrawl
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->groupRepository = $groupRepository;
        $this->phpRelatedFilter = $phpRelatedFilter;
        $this->topicsToCrawl = $topicsToCrawl;

        $this->countryCodesWithNoPhpGroups = $this->loadCountryCodesWithNoPhpGroups();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Crawl "meetup.com" api topic lists by every country.');
    }

    /**
     * Api is broken, thus useles here @see https://github.com/meetup/api/issues/249s
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (CountryLoader::countries() as $country) {
            /** @var Country $country */
            $country = CountryLoader::country($country['iso_3166_1_alpha2']);

            if ($this->shouldSkipCountry($country)) {
                continue;
            }

            $this->symfonyStyle->note(sprintf('Looking for meetups in "%s"', $country->getName()));

            foreach ($this->topicsToCrawl as $keyword) {
                $this->processKeywordAndCountry($keyword, strtolower($country->getIsoAlpha2()));
            }
        }

        // detect country codes that were empty on "PHP" search
        // by excluding them, the followup search with other keywords can be faster
        $this->reportEmptyCountries();
        $this->reportFoundGroups();

        $this->symfonyStyle->success('OK');

        return 0;
    }

    /**
     * @return string[]
     */
    private function loadCountryCodesWithNoPhpGroups(): array
    {
        $data = FileSystem::read(__DIR__ . '/../data/empty_countries_on_php.txt');
        return explode(PHP_EOL, $data);
    }

    private function shouldSkipCountry(Country $country): bool
    {
        if ($country->getIsoAlpha2() === null) {
            return true;
        }

        $countryCode = strtolower($country->getIsoAlpha2());

        return in_array($countryCode, $this->countryCodesWithNoPhpGroups, true);
    }

    private function processKeywordAndCountry(string $keyword, string $countryCode): void
    {
        $crawlUrl = sprintf('https://www.meetup.com/topics/%s/%s/', $keyword, $countryCode);
        $this->symfonyStyle->writeln(' * ' . $crawlUrl);

        // init
        if (! isset($this->groupsByCountry[$countryCode])) {
            $this->groupsByCountry[$countryCode] = [];
        }

        $crawler = $this->createCrawlerFromUrl($crawlUrl);

        // top 10 overall → nothing found and fallback to main page → skip
        if (Strings::contains($crawler->text(), 'SQL NYC, The NoSQL & NewSQL Database Meetup')) {
            return;
        }

        $this->collectGroups($crawler, $countryCode);
    }

    private function reportEmptyCountries(): void
    {
        $this->symfonyStyle->section('Empty country codes');

        foreach ($this->groupsByCountry as $country => $groups) {
            if (count($groups)) {
                continue;
            }

            $this->symfonyStyle->writeln($country);
        }
    }

    private function reportFoundGroups(): void
    {
        $this->symfonyStyle->section('Found groups');

        foreach ($this->groupsByCountry as $groups) {
            $groups = Arrays::unique($groups);
            $groups = $this->phpRelatedFilter->filterGroups($groups);

            foreach ($groups as $group) {
                $this->symfonyStyle->writeln('    -   name: "' . str_replace('"', "'", $group[Group::NAME]) . '"');
                $this->symfonyStyle->writeln('        meetup_com_url: ' . $group[Group::URL]);
                $this->symfonyStyle->newLine();
            }
        }
    }

    private function createCrawlerFromUrl(string $url): Crawler
    {
        return new Crawler(FileSystem::read($url));
    }

    private function collectGroups(Crawler $crawler, string $countryCode): void
    {
        $crawler->filterXPath('//span[@class="spreadable-item attachment"]')->each(
            function (Crawler $node) use ($countryCode): void {
                $groupUrl = $node->filterXPath('//a/@href')->text();

                // is already among groups?
                if ($this->groupRepository->findByUrl($groupUrl)) {
                    return;
                }

                // headlines + urls of found groups
                $this->groupsByCountry[$countryCode][] = [
                    Group::NAME => $node->filterXPath('//span[@class="text--bold display--block"]')->text(),
                    Group::URL => $groupUrl,
                ];
            }
        );
    }
}
