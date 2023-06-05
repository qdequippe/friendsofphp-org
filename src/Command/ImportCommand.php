<?php

declare(strict_types=1);

namespace Fop\Command;

use Fop\Exception\ShouldNotHappenException;
use Fop\Meetup\Filter\MeetupFilter;
use Fop\Meetup\Repository\GroupRepository;
use Fop\Meetup\Repository\MeetupRepository;
use Fop\Meetup\ValueObject\Meetup;
use Fop\MeetupCom\MeetupComMeetupImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ImportCommand extends Command
{
    public function __construct(
        private readonly MeetupRepository $meetupRepository,
        private readonly MeetupFilter $meetupFilter,
        private readonly MeetupComMeetupImporter $meetupComMeetupImporter,
        private readonly GroupRepository $groupRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('import');
        $this->setDescription('Import meetups from meetup.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $title = sprintf('Importing meetups from %d PHP groups from "meetup.com"', $this->groupRepository->getCount());
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title($title);

        $meetups = $this->meetupComMeetupImporter->import($symfonyStyle);
        $meetups = $this->meetupFilter->filter($meetups);

        $this->reportFoundMeetups($meetups, $symfonyStyle);

        $this->meetupRepository->deleteAll();
        $this->meetupRepository->saveMany($meetups);

        return self::SUCCESS;
    }

    /**
     * @param Meetup[] $meetups
     */
    private function reportFoundMeetups(array $meetups, SymfonyStyle $symfonyStyle): void
    {
        if ($meetups === []) {
            throw new ShouldNotHappenException('No meetups found - that is very unlikely. Is Meetup.com up?');
        }

        $successMessage = sprintf('Loaded %d meetups', count($meetups));
        $symfonyStyle->success($successMessage);

        $meetupListToDisplay = [];
        foreach ($meetups as $meetup) {
            $meetupListToDisplay[] = sprintf(
                '%s - %s (by "%s" group)',
                $meetup->getLocalDate(),
                $meetup->getName(),
                $meetup->getUserGroup()
            );
        }

        $symfonyStyle->listing($meetupListToDisplay);
    }
}
