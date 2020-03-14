<?php declare(strict_types=1);

namespace Fop\Core\Command;

use Fop\Meetup\Contract\MeetupImporterInterface;
use Fop\Meetup\DataCollector\MeetupCollector;
use Fop\Meetup\Filter\MeetupFilterCollector;
use Fop\Meetup\Repository\MeetupRepository;
use Fop\Meetup\ValueObject\Meetup;
use Fop\MeetupCom\Command\Reporter\MeetupReporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ImportCommand extends Command
{
    /**
     * @var string
     */
    private const OPTION_ONLY = 'only';

    private SymfonyStyle $symfonyStyle;

    private MeetupRepository $meetupRepository;

    private MeetupReporter $meetupReporter;

    private MeetupFilterCollector $meetupFilterCollector;

    private MeetupCollector $meetupCollector;

    /**
     * @var MeetupImporterInterface[]
     */
    private array $meetupImporters = [];

    /**
     * @param MeetupImporterInterface[] $meetupImporters
     */
    public function __construct(
        array $meetupImporters,
        SymfonyStyle $symfonyStyle,
        MeetupRepository $meetupRepository,
        MeetupReporter $meetupReporter,
        MeetupFilterCollector $meetupFilterCollector,
        MeetupCollector $meetupCollector
    ) {
        parent::__construct();
        $this->meetupImporters = $meetupImporters;
        $this->symfonyStyle = $symfonyStyle;
        $this->meetupRepository = $meetupRepository;
        $this->meetupReporter = $meetupReporter;
        $this->meetupFilterCollector = $meetupFilterCollector;
        $this->meetupCollector = $meetupCollector;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import meetups from meetup providers');
        $this->addOption(self::OPTION_ONLY, null, InputOption::VALUE_REQUIRED, 'Single provider key to run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $provider */
        $provider = $input->getOption(self::OPTION_ONLY);

        $meetupImporters = $this->getMeetupImporters($provider);

        if ($meetupImporters === [] && $provider) {
            $this->symfonyStyle->error(sprintf(
                'The provider for "%s" was not found.%sPick one of "%s"',
                $provider,
                PHP_EOL,
                implode('", "', $this->getMeetupImporterKeys())
            ));
            return ShellCode::ERROR;
        }

        foreach ($meetupImporters as $meetupImporter) {
            $this->symfonyStyle->section(sprintf('Importing meetups from "%s"', $meetupImporter->getKey()));

            $meetups = $meetupImporter->getMeetups();
            $meetups = $this->meetupFilterCollector->filter($meetups);

            $this->reportMeetups($meetups, $meetupImporter->getKey());
            $this->meetupCollector->addMeetups($meetups);

            $this->symfonyStyle->newLine(2);
        }

        $this->meetupRepository->saveImportsToFile($this->meetupCollector->getMeetups());

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
     * @return string[]
     */
    private function getMeetupImporterKeys(): array
    {
        $keys = [];
        foreach ($this->meetupImporters as $meetupImporter) {
            $keys[] = $meetupImporter->getKey();
        }

        return $keys;
    }

    /**
     * @param Meetup[] $meetups
     */
    private function reportMeetups(array $meetups, string $key): void
    {
        if (count($meetups) === 0) {
            return;
        }

        $this->meetupReporter->printMeetups($meetups);

        $this->symfonyStyle->success(sprintf('Loaded %d meetups from "%s"', count($meetups), $key));
    }
}
