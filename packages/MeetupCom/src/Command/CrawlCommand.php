<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Entity\Group;
use Fop\MeetupCom\Command\Reporter\GroupReporter;
use Fop\MeetupCom\Filter\PhpRelatedFilter;
use Fop\MeetupCom\Group\GroupDetailResolver;
use Fop\Repository\GroupRepository;
use Fop\Utils\Arrays;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rinvex\Country\Country;
use Rinvex\Country\CountryLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class CrawlCommand extends Command
{
    /**
     * @var string
     */
    private const XML_CONDOM = '<?xml version="1.0" encoding="utf-8"?>';

    /**
     * @var string[]
     */
    private $usaStates = [];

    /**
     * @var string[]
     */
    private $topicsToCrawl = [];

    /**
     * @var mixed[][]
     */
    private $groups = [];

    /**
     * @var string[]
     */
    private $invalidCountryCodes = [];

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
     * @var GroupDetailResolver
     */
    private $groupDetailResolver;

    /**
     * @var GroupReporter
     */
    private $groupReporter;

    /**
     * @param string[] $topicsToCrawl
     * @param string[] $usaStates
     * @param string[] $invalidCountryCodes
     */
    public function __construct(
        SymfonyStyle $symfonyStyle,
        GroupRepository $groupRepository,
        PhpRelatedFilter $phpRelatedFilter,
        GroupDetailResolver $groupDetailResolver,
        GroupReporter $groupReporter,
        array $topicsToCrawl,
        array $usaStates,
        array $invalidCountryCodes
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->groupRepository = $groupRepository;
        $this->phpRelatedFilter = $phpRelatedFilter;
        $this->topicsToCrawl = $topicsToCrawl;

        $this->usaStates = $usaStates;
        $this->groupDetailResolver = $groupDetailResolver;
        $this->groupReporter = $groupReporter;
        $this->invalidCountryCodes = $invalidCountryCodes;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Crawl "meetup.com" api topic lists by every country.');
        $this->addOption('usa', null, InputOption::VALUE_NONE, 'Run on USA only, for performance reasons');
        $this->addOption('non-usa', null, InputOption::VALUE_NONE, 'Run on non-USA only, for performance reasons');
    }

    /**
     * Api is broken, thus useles here @see https://github.com/meetup/api/issues/249s
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isUsaOnly = (bool) $input->getOption('usa');
        $isNonUsaOnly = (bool) $input->getOption('non-usa');

        if ($isNonUsaOnly === false) {
            $this->processUnitedStatesOfAmerica();
        }

        if ($isUsaOnly === false) {
            $this->processNonUsaCountries();
        }

        $this->reportFoundGroups();

        $this->symfonyStyle->success('Crawling was successful');

        return ShellCode::SUCCESS;
    }

    /**
     * @see https://en.wikipedia.org/wiki/ISO_3166-2:US
     */
    private function processUnitedStatesOfAmerica(): void
    {
        foreach (array_keys($this->usaStates) as $usaStateCode) {
            $usaStateCode = strtolower($usaStateCode);
            $this->processKeywordAndStateInUsa('php', $usaStateCode);
        }
    }

    private function processNonUsaCountries(): void
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
    }

    private function reportFoundGroups(): void
    {
        $this->symfonyStyle->section('Found groups');

        $groups = Arrays::unique($this->groups);
        $groups = $this->phpRelatedFilter->filterGroups($groups);

        foreach ($groups as $group) {
            $group = $this->groupDetailResolver->resolveFromUrl($group[Group::URL]);
            $this->groupReporter->printGroup($group);
        }
    }

    private function processKeywordAndStateInUsa(string $keyword, string $state): void
    {
        $stateCityUrls = $this->collectUsaStateCityUrls($keyword, $state);

        foreach ($stateCityUrls as $stateCityUrl) {
            $crawler = $this->createCrawlerFromUrl($stateCityUrl);
            if ($crawler === null) {
                continue;
            }

            // top 10 overall → nothing found and fallback to main page → skip
            if (Strings::contains($crawler->text(), 'There are no Meetups matching this search')) {
                continue;
            }

            $this->symfonyStyle->writeln(' * ' . $stateCityUrl);

            // @see https://stackoverflow.com/a/8681157/1348344
            $crawler->filterXPath('//li[contains(@class,"groupCard")]')->each(
                function (Crawler $node): void {
                    $groupUrl = $node->filterXPath('//a/@href')->text();

                    // is already among groups?
                    if ($this->groupRepository->findByUrl($groupUrl)) {
                        return;
                    }

                    // headlines + urls of found groups
                    $this->groups[] = [
                        Group::NAME => $groupName = $node->filterXPath('//a')->text(),
                        Group::URL => $groupUrl,
                    ];
                }
            );
        }
    }

    private function shouldSkipCountry(Country $country): bool
    {
        if ($country->getIsoAlpha2() === null) {
            return true;
        }

        // non-existing countries at meetup.com
        $countryCode = strtolower($country->getIsoAlpha2());

        return in_array($countryCode, $this->invalidCountryCodes, true);
    }

    private function processKeywordAndCountry(string $keyword, string $countryCode): void
    {
        $crawlUrl = sprintf('https://www.meetup.com/topics/%s/%s/', $keyword, $countryCode);
        $this->symfonyStyle->writeln(' * ' . $crawlUrl);

        $crawler = $this->createCrawlerFromUrl($crawlUrl);
        if ($crawler === null) {
            return;
        }

        // top 10 overall → nothing found and fallback to main page → skip
        if (Strings::contains($crawler->text(), 'SQL NYC, The NoSQL & NewSQL Database Meetup')) {
            return;
        }

        $this->collectGroups($crawler);
    }

    /**
     * @return string[]
     */
    private function collectUsaStateCityUrls(string $keyword, string $state): array
    {
        $stateCityUrls = [];

        $crawlUrl = sprintf('https://www.meetup.com/topics/%s/us/%s/', $keyword, $state);
        $this->symfonyStyle->writeln(' * ' . $crawlUrl);

        $crawler = $this->createCrawlerFromUrl($crawlUrl);
        if ($crawler === null) {
            return [];
        }

        $crawler->filterXPath('//li[@class="gridList-item"]')->each(
            function (Crawler $node): void {
                $stateCityUrls[] = $node->filterXPath('//a/@href')->text();
            }
        );

        return $stateCityUrls;
    }

    private function createCrawlerFromUrl(string $url): ?Crawler
    {
        try {
            $remoteContent = trim(FileSystem::read($url));
        } catch (IOException $iOException) {
            $this->symfonyStyle->error($iOException->getMessage());
            return null;
        }

        if (Strings::startsWith($remoteContent, self::XML_CONDOM)) {
            $remoteContent = Strings::substring($remoteContent, strlen(self::XML_CONDOM));
            $remoteContent = trim($remoteContent);
        }

        return new Crawler($remoteContent);
    }

    private function collectGroups(Crawler $crawler): void
    {
        $crawler->filterXPath('//span[@class="spreadable-item attachment"]')->each(
            function (Crawler $node): void {
                $groupUrl = $node->filterXPath('//a/@href')->text();

                // is already among groups?
                if ($this->groupRepository->findByUrl($groupUrl)) {
                    return;
                }

                // headlines + urls of found groups
                $this->groups[] = [
                    Group::NAME => $node->filterXPath('//span[@class="text--bold display--block"]')->text(),
                    Group::URL => $groupUrl,
                ];
            }
        );
    }
}
