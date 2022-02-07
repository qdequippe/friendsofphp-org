<?php

declare(strict_types=1);

namespace Fop\Core\Command;

use Fop\Meetup\DataCollector\MeetupCollector;
use Fop\Meetup\Filter\MeetupFilterCollector;
use Fop\Meetup\Repository\MeetupRepository;
use Fop\MeetupCom\Command\Reporter\MeetupReporter;
use Fop\MeetupCom\MeetupComMeetupImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly MeetupRepository $meetupRepository,
        private readonly MeetupReporter $meetupReporter,
        private readonly MeetupFilterCollector $meetupFilterCollector,
        private readonly MeetupCollector $meetupCollector,
        private readonly MeetupComMeetupImporter $meetupComMeetupImporter
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
        $this->symfonyStyle->title('Importing meetups from "meetup.com"');

        $meetups = $this->meetupComMeetupImporter->getMeetups();
        $meetups = $this->meetupFilterCollector->filter($meetups);

        $this->meetupReporter->reportMeetups($meetups);
        $this->meetupCollector->addMeetups($meetups);

        $this->symfonyStyle->newLine(2);

        $this->meetupRepository->saveMany($this->meetupCollector->getMeetups());

        $this->symfonyStyle->success('Import is done!');

        return self::SUCCESS;
    }
}
