<?php declare(strict_types=1);

namespace Fop\CrosswebPl\Command;

use DateTimeInterface;
use Fop\CrosswebPl\Meetup\CrosswebPlMeetupFactory;
use Fop\DouUa\Xml\XmlReader;
use Fop\Repository\MeetupRepository;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ImportCrosswebPlCommand extends Command
{
    /**
     * @var string
     */
    private const XML_CALENDAR_FEED = 'https://crossweb.pl/feed/wydarzenia/php/';

    /**
     * @var int
     */
    private $maxForecastDays;

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

    /**
     * @var CrosswebPlMeetupFactory
     */
    private $crosswebPlMeetupFactory;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        int $maxForecastDays,
        MeetupRepository $meetupRepository,
        XmlReader $xmlReader,
        CrosswebPlMeetupFactory $crosswebPlMeetupFactory
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->maxForecastDays = $maxForecastDays;
        $this->maxForecastDateTime = DateTime::from('+' . $maxForecastDays . 'days');

        $this->meetupRepository = $meetupRepository;
        $this->xmlReader = $xmlReader;
        $this->crosswebPlMeetupFactory = $crosswebPlMeetupFactory;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Imports events from https://crossweb.pl/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

            // skip meetups too far in the future
            if ($meetup->getStartDateTime() > $this->maxForecastDateTime) {
                continue;
            }

            $meetups[] = $meetup;
        }

        $this->meetupRepository->saveImportsToFile($meetups, 'dou-ua');

        $this->symfonyStyle->note(
            sprintf('Loaded %d meetups for next %d days', count($meetups), $this->maxForecastDays)
        );
        $this->symfonyStyle->success('Done');

        return ShellCode::SUCCESS;
    }

    /**
     * clear "utm_source", not needed
     */
    private function clearUrl(string $url): string
    {
        return Strings::replace($url, '#(https.*?)\?utm.*?$#', '$1');
    }
}
