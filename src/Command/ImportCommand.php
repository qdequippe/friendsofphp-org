<?php

declare(strict_types=1);

namespace Fop\Core\Command;

use Fop\Meetup\Contract\MeetupImporterInterface;
use Fop\Meetup\DataCollector\MeetupCollector;
use Fop\Meetup\Filter\MeetupFilterCollector;
use Fop\Meetup\Repository\MeetupRepository;
use Fop\MeetupCom\Command\Reporter\MeetupReporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportCommand extends Command
{
    /**
     * @param MeetupImporterInterface[] $meetupImporters
     */
    public function __construct(
        private array $meetupImporters,
        private SymfonyStyle $symfonyStyle,
        private MeetupRepository $meetupRepository,
        private MeetupReporter $meetupReporter,
        private MeetupFilterCollector $meetupFilterCollector,
        private MeetupCollector $meetupCollector
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import meetups from meetup providers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->meetupImporters as $meetupImporter) {
            $section = sprintf('Importing meetups from "%s"', $meetupImporter->getKey());
            $this->symfonyStyle->section($section);

            $meetups = $meetupImporter->getMeetups();
            $meetups = $this->meetupFilterCollector->filter($meetups);

            $this->meetupReporter->reportMeetups($meetups, $meetupImporter->getKey());
            $this->meetupCollector->addMeetups($meetups);

            $this->symfonyStyle->newLine(2);
        }

        $this->meetupRepository->saveMany($this->meetupCollector->getMeetups());

        $this->symfonyStyle->success('Import is done!');

        return self::SUCCESS;
    }
}
