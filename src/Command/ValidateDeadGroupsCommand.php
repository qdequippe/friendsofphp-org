<?php

declare(strict_types=1);

namespace Fop\Command;

use DateTimeInterface;
use Fop\Meetup\Repository\GroupRepository;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Api\MeetupComCooler;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $this->setName('validate-dead-groups');
        $this->setDescription('Import the last group meetup from meetup.com to see which are active and which not');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $possiblyDeadGroups = [];

        $manyMonthsAgoDateTime = DateTime::from('-6 months');

        foreach ($this->groupRepository->fetchAll() as $group) {
            $lastMeetupDateTime = $this->meetupComApi->getLastMeetupDateTimeByGroupSlug($group->getMeetupComSlug());
            if (! $lastMeetupDateTime instanceof DateTimeInterface) {
                $possiblyDeadGroups[$group->getName()] = 'none';
                continue;
            }

            $message = sprintf('* Last meetup for "%s": %s', $group->getName(), $lastMeetupDateTime->format('Y-m-d'));
            $this->symfonyStyle->writeln($message);

            // too fresh
            if ($lastMeetupDateTime > $manyMonthsAgoDateTime) {
                $this->meetupComCooler->coolDownIfNeeded();
                continue;
            }

            $this->meetupComCooler->coolDownIfNeeded();
            $possiblyDeadGroups[$group->getName()] = $lastMeetupDateTime->format('Y-m-d');
        }

        if ($possiblyDeadGroups === []) {
            $this->symfonyStyle->success('All groups are fresh!');

            return self::SUCCESS;
        }

        $section = sprintf('There are %d dead groups', count($possiblyDeadGroups));
        $this->symfonyStyle->section($section);

        foreach ($possiblyDeadGroups as $groupName => $lastMeetupDateTimeAsString) {
            $groupMessage = sprintf(' * group "%s" with last meetup on %s', $groupName, $lastMeetupDateTimeAsString);
            $this->symfonyStyle->writeln($groupMessage);
        }

        return self::FAILURE;
    }
}
