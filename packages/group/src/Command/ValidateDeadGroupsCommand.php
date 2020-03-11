<?php declare(strict_types=1);

namespace Fop\Group\Command;

use DateTime;
use Fop\MeetupCom\Api\MeetupComCooler;
use Fop\Group\Repository\GroupRepository;
use Fop\MeetupCom\Api\MeetupComApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ValidateDeadGroupsCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    /**
     * @var MeetupComCooler
     */
    private $meetupComCooler;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        GroupRepository $groupRepository,
        MeetupComApi $meetupComApi,
        MeetupComCooler $meetupComCooler
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->groupRepository = $groupRepository;
        $this->meetupComApi = $meetupComApi;
        $this->meetupComCooler = $meetupComCooler;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import the last group meetup from meetup.com to see which are active and which not');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $possiblyDeadGroups = [];
        $sixMonthsAgoDateTime = new DateTime('- 6 months');

        foreach ($this->groupRepository->fetchAll() as $group) {
            $lastMeetupDateTime = $this->meetupComApi->getLastMeetupDateTimeByGroupSlug($group->getMeetupComSlug());

            $this->symfonyStyle->note(sprintf('Resolved last meetup date time for "%s"', $group->getName()));

            // too fresh
            if ($lastMeetupDateTime > $sixMonthsAgoDateTime) {
                $this->meetupComCooler->coolDownIfNeeded();
                continue;
            }

            $lastMeetupDateTimeAsString = $lastMeetupDateTime ? $lastMeetupDateTime->format('Y-m-d') : '';
            $possiblyDeadGroups[$group->getName()] = $lastMeetupDateTimeAsString;

            $this->meetupComCooler->coolDownIfNeeded();
        }

        if ($possiblyDeadGroups === []) {
            $this->symfonyStyle->success('All groups are fresh!');

            return ShellCode::SUCCESS;
        }

        $this->symfonyStyle->section(sprintf('There are %d dead groups', count($possiblyDeadGroups)));

        foreach ($possiblyDeadGroups as $groupName => $lastMeetupDateTimeAsString) {
            $this->symfonyStyle->writeln(
                sprintf(' * group "%s" with last meetup on %d', $groupName, $lastMeetupDateTimeAsString)
            );
        }

        return ShellCode::ERROR;
    }
}
