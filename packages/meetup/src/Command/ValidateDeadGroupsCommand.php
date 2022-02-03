<?php

declare(strict_types=1);

namespace Fop\Meetup\Command;

use Fop\Meetup\Repository\GroupRepository;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Api\MeetupComCooler;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ValidateDeadGroupsCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly GroupRepository $groupRepository,
        private readonly MeetupComApi $meetupComApi,
        private readonly MeetupComCooler $meetupComCooler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import the last group meetup from meetup.com to see which are active and which not');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $possiblyDeadGroups = [];
        // increase temporary from 16 months due to covid
        $sixMonthsAgoDateTime = DateTime::from('- 16 months');

        foreach ($this->groupRepository->getGroups() as $group) {
            $lastMeetupDateTime = $this->meetupComApi->getLastMeetupDateTimeByGroupSlug($group->getMeetupComSlug());

            $message = sprintf('Resolved last meetup date time for "%s"', $group->getName());
            $this->symfonyStyle->note($message);

            // too fresh
            if ($lastMeetupDateTime > $sixMonthsAgoDateTime) {
                $this->meetupComCooler->coolDownIfNeeded();
                continue;
            }

            $lastMeetupDateTimeAsString = $lastMeetupDateTime !== null ? $lastMeetupDateTime->format('Y-m-d') : '';
            $possiblyDeadGroups[$group->getName()] = $lastMeetupDateTimeAsString;

            $this->meetupComCooler->coolDownIfNeeded();
        }

        if ($possiblyDeadGroups === []) {
            $this->symfonyStyle->success('All groups are fresh!');

            return self::SUCCESS;
        }

        $section = sprintf('There are %d dead groups', count($possiblyDeadGroups));
        $this->symfonyStyle->section($section);

        foreach ($possiblyDeadGroups as $groupName => $lastMeetupDateTimeAsString) {
            $groupMessage = sprintf(' * group "%s" with last meetup on %d', $groupName, $lastMeetupDateTimeAsString);
            $this->symfonyStyle->writeln($groupMessage);
        }

        return self::FAILURE;
    }
}
