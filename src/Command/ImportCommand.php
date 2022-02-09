<?php

declare(strict_types=1);

namespace Fop\Core\Command;

use Fop\Meetup\Filter\MeetupFilterCollector;
use Fop\Meetup\Repository\GroupRepository;
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
        private readonly MeetupComMeetupImporter $meetupComMeetupImporter,
        private readonly GroupRepository $groupRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import meetups from meetup.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $title = sprintf('Importing meetups from %d PHP groups from "meetup.com"', $this->groupRepository->getCount());
        $this->symfonyStyle->title($title);

        $meetups = $this->meetupComMeetupImporter->import();
        $meetups = $this->meetupFilterCollector->filter($meetups);

        $this->meetupReporter->reportMeetups($meetups);

        $this->meetupRepository->saveMany($meetups);

        return self::SUCCESS;
    }
}
