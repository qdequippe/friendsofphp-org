<?php

declare(strict_types=1);

namespace Fop\Core\Command;

use Fop\Meetup\Filter\MeetupFilterCollector;
use Fop\Meetup\Repository\GroupRepository;
use Fop\Meetup\Repository\MeetupRepository;
use Fop\Meetup\ValueObject\Meetup;
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

        $this->reportFoundMeetups($meetups);

        $this->meetupRepository->deleteAll();
        $this->meetupRepository->saveMany($meetups);

        return self::SUCCESS;
    }

    /**
     * @param Meetup[] $meetups
     */
    private function reportFoundMeetups(array $meetups): void
    {
        if ($meetups === []) {
            $this->symfonyStyle->warning('No meetups found - that is very unlikely. Is Meetup.com API working?');
            return;
        }

        $successMessage = sprintf('Loaded %d meetups', count($meetups));
        $this->symfonyStyle->success($successMessage);

        $meetupListToDisplay = [];
        foreach ($meetups as $meetup) {
            $meetupListToDisplay[] = sprintf(
                '%s - %s (by "%s" group)',
                $meetup->getStartDateTimeFormatted('Y-m-d'),
                $meetup->getName(),
                $meetup->getUserGroup()
            );
        }

        $this->symfonyStyle->listing($meetupListToDisplay);
    }
}
