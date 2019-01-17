<?php declare(strict_types=1);

namespace Fop\DouUa\Command;

use Fop\Command\AbstractImportCommand;
use Fop\DouUa\Meetup\DouUaMeetupFactory;
use Fop\DouUa\Xml\XmlReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ImportDouUaCommand extends AbstractImportCommand
{
    /**
     * @var string
     */
    private const XML_CALENDAR_FEED = 'https://dou.ua/calendar/feed/PHP/';

    /**
     * @var XmlReader
     */
    private $xmlReader;

    /**
     * @var DouUaMeetupFactory
     */
    private $douUaMeetupFactory;

    public function __construct(XmlReader $xmlReader, DouUaMeetupFactory $douUaMeetupFactory)
    {
        parent::__construct();
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

        $this->saveAndReportMeetups($meetups);

        return ShellCode::SUCCESS;
    }

    protected function getSourceName(): string
    {
        return 'dou-ua';
    }
}
