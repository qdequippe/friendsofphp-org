<?php declare(strict_types=1);

namespace Fop\DouUa\Command;

use DateTimeInterface;
use Fop\DouUa\Meetup\DouUaMeetupFactory;
use Fop\DouUa\Xml\XmlReader;
use Fop\Repository\MeetupRepository;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
     * @var DouUaMeetupFactory
     */
    private $douUaMeetupFactory;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        int $maxForecastDays,
        MeetupRepository $meetupRepository,
        XmlReader $xmlReader,
        DouUaMeetupFactory $douUaMeetupFactory
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->maxForecastDays = $maxForecastDays;
        $this->maxForecastDateTime = DateTime::from('+' . $maxForecastDays . 'days');

        $this->meetupRepository = $meetupRepository;
        $this->xmlReader = $xmlReader;
        $this->douUaMeetupFactory = $douUaMeetupFactory;
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

            $meetup = $this->douUaMeetupFactory->createMeetupFromUrlAndName($url, $name);
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
}
