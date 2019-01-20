<?php declare(strict_types=1);

namespace Fop\OpentechcalendarCoUk\Command;

use Fop\Command\AbstractImportCommand;
use Fop\OpentechcalendarCoUk\Factory\MeetupFactory;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ImportOpentechcalendarCoUkCommand extends AbstractImportCommand
{
    /**
     * @var string
     */
    private const EVENTS_JSON_URL = 'https://opentechcalendar.co.uk/api1/events.json';

    /**
     * @var MeetupFactory
     */
    private $meetupFactory;

    public function __construct(MeetupFactory $meetupFactory)
    {
        parent::__construct();
        $this->meetupFactory = $meetupFactory;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Imports events from https://opentechcalendar.co.uk/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jsonContent = FileSystem::read(self::EVENTS_JSON_URL);
        $json = Json::decode($jsonContent, Json::FORCE_ARRAY);

        $eventsJson = $json['data'] ?? [];

        $meetups = [];
        foreach ($eventsJson as $eventJson) {
            if (! Strings::match($eventJson['summary'], '#\b(php|wordpress)\b#i')) {
                continue;
            }

            $meetup = $this->meetupFactory->createFromArray($eventJson);
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
        return 'opentechcalendar-co-uk';
    }
}
