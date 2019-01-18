<?php declare(strict_types=1);

namespace Fop\PosobotaCz\Command;

use Fop\Command\AbstractImportCommand;
use Fop\Entity\Meetup;
use Fop\Geolocation\Geolocator;
use Fop\PosobotaCz\IcalParser;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ImportPosobotaCzCommand extends AbstractImportCommand
{
    /**
     * @var string
     */
    private const LAST_EVENT_CALENDAR = 'https://www.posobota.cz/feed.ical.php';

    /**
     * @var Geolocator
     */
    private $geolocator;

    /**
     * @var IcalParser
     */
    private $icalParser;

    public function __construct(Geolocator $geolocator, IcalParser $icalParser)
    {
        parent::__construct();
        $this->geolocator = $geolocator;
        $this->icalParser = $icalParser;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Imports events from https://posobota.cz/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = $this->icalParser->parseIcalUrlToArray(self::LAST_EVENT_CALENDAR);

        $name = $this->resolveName($data['UID']);
        $date = DateTime::from($data['DTSTAMP']);
        $location = $this->geolocator->createLocationFromCity($data['LOCATION']);
        if ($location === null) {
            return ShellCode::ERROR;
        }

        $url = $data['URL'];

        $meetup = new Meetup($name, 'Posobota', $date, $location, $url);

        // skip meetups too far in the future
        if ($meetup->getStartDateTime() > $this->maxForecastDateTime) {
            $this->symfonyStyle->note('No new meetup');

            return ShellCode::SUCCESS;
        }

        $this->saveAndReportMeetups([$meetup]);

        return ShellCode::SUCCESS;
    }

    protected function getSourceName(): string
    {
        return 'posobota-cz';
    }

    private function resolveName(string $uid): string
    {
        $match = Strings::match($uid, '#(?<id>.*?)@#');
        $id = $match['id'] ?? '';

        return 'Poslední Sobota ' . $id;
    }
}
