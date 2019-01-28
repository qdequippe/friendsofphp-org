<?php declare(strict_types=1);

namespace Fop\Command;

use DateTimeInterface;
use Fop\Contract\MeetupImporterInterface;
use Fop\Entity\Meetup;
use Fop\MeetupCom\Command\Reporter\MeetupReporter;
use Fop\Repository\MeetupRepository;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use function Safe\sprintf;

final class ImportCommand extends Command
{
    /**
     * @var MeetupImporterInterface[]
     */
    private $meetupImporters = [];

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
     * @var MeetupReporter
     */
    private $meetupReporter;

    /**
     * @param MeetupImporterInterface[] $meetupImporters
     */
    public function __construct(
        array $meetupImporters,
        int $maxForecastDays,
        SymfonyStyle $symfonyStyle,
        MeetupRepository $meetupRepository,
        MeetupReporter $meetupReporter
) {
        parent::__construct();
        $this->meetupImporters = $meetupImporters;
        $this->symfonyStyle = $symfonyStyle;
        $this->meetupRepository = $meetupRepository;
        $this->meetupReporter = $meetupReporter;

        $this->maxForecastDateTime = DateTime::from('+' . $maxForecastDays . 'days');
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import meetups from meetup providers');
        $this->addOption('only', null, InputOption::VALUE_REQUIRED, 'Single provider key to run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $provider */
        $provider = $input->getOption('only');

        $meetupImporters = $this->getMeetupImporters($provider);

        foreach ($meetupImporters as $meetupImporter) {
            $this->symfonyStyle->note(sprintf('Importing meetups from "%s"', $meetupImporter->getKey()));

            $meetups = $meetupImporter->getMeetups();
            $meetups = $this->filterOutTooFarMeetups($meetups);

            $this->saveAndReportMeetups($meetups, $meetupImporter->getKey());
        }

        $this->symfonyStyle->success('Import is done!');

        return ShellCode::SUCCESS;
    }

    /**
     * @return MeetupImporterInterface[]
     */
    private function getMeetupImporters(?string $provider): array
    {
        if ($provider === null) {
            return $this->meetupImporters;
        }

        foreach ($this->meetupImporters as $meetupImporter) {
            if ($meetupImporter->getKey() === $provider) {
                return [$meetupImporter];
            }
        }

        // none found
        return [];
    }

    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    private function filterOutTooFarMeetups(array $meetups): array
    {
        return array_filter($meetups, function (Meetup $meetup) {
            // filter out past meetups
            if ($meetup->getStartDateTime() <= DateTime::from('now')) {
                return false;
            }

            return $meetup->getStartDateTime() <= $this->maxForecastDateTime;
        });
    }

    /**
     * @param Meetup[] $meetups
     */
    private function saveAndReportMeetups(array $meetups, string $key): void
    {
        if (count($meetups) === 0) {
            return;
        }

        $this->meetupReporter->printMeetups($meetups);
        $this->meetupRepository->saveImportsToFile($meetups, $key);

        $this->symfonyStyle->success(sprintf('Loaded %d meetups from "%s"', count($meetups), $key));
    }
}
