<?php declare(strict_types=1);

namespace Fop\Group\Command;

use Fop\Group\Repository\GroupRepository;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Api\MeetupComCooler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class LastGroupMeetupCommand extends Command
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
        $groups = $this->groupRepository->fetchAll();

        foreach ($groups as $group) {
            $lastMeetupDateTime = $this->meetupComApi->getLastMeetupDateTimeByGroupSlug($group->getMeetupComSlug());
            $lastMeetupDateTimeAsString = $lastMeetupDateTime ? $lastMeetupDateTime->format('Y-m-d') : '';

            $this->symfonyStyle->note(
                sprintf(
                    'Update last meetup date time for "%s" group to "%s"',
                    $group->getName(),
                    $lastMeetupDateTimeAsString
                )
            );

            $group->changeLastMeetupDateTime($lastMeetupDateTime);

            $this->meetupComCooler->coolDownIfNeeded();
        }

        $this->groupRepository->persist();
        $this->symfonyStyle->success('Finished');

        return ShellCode::SUCCESS;
    }
}
