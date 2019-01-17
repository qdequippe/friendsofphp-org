<?php declare(strict_types=1);

namespace Fop\CrosswebPl\Command;

use Fop\Command\AbstractImportCommand;
use Fop\CrosswebPl\Meetup\CrosswebPlMeetupFactory;
use Fop\DouUa\Xml\XmlReader;
use Nette\Utils\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ImportCrosswebPlCommand extends AbstractImportCommand
{
    /**
     * @var string
     */
    private const XML_CALENDAR_FEED = 'https://crossweb.pl/feed/wydarzenia/php/';

    /**
     * @var XmlReader
     */
    private $xmlReader;

    /**
     * @var CrosswebPlMeetupFactory
     */
    private $crosswebPlMeetupFactory;

    public function __construct(XmlReader $xmlReader, CrosswebPlMeetupFactory $crosswebPlMeetupFactory)
    {
        parent::__construct();
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

        $this->saveAndReportMeetups($meetups);

        return ShellCode::SUCCESS;
    }

    protected function getSourceName(): string
    {
        return 'crossweb-pl';
    }

    /**
     * clear "utm_source", not needed
     */
    private function clearUrl(string $url): string
    {
        return Strings::replace($url, '#(https.*?)\?utm.*?$#', '$1');
    }
}
