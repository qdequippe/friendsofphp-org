<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Command\AbstractImportCommand;
use Fop\MeetupCom\MeetupImporter;
use Fop\Repository\GroupRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ImportMeetupComCommand extends AbstractImportCommand
{
    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var MeetupImporter
     */
    private $meetupImporter;

    public function __construct(GroupRepository $userGroupRepository, MeetupImporter $meetupImporter)
    {
        parent::__construct();
        $this->groupRepository = $userGroupRepository;
        $this->meetupImporter = $meetupImporter;
    }

    public function getSourceName(): string
    {
        return 'meetup-com';
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import meetups from https://meetup.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $groups = $this->groupRepository->fetchAll();

        $this->symfonyStyle->note(sprintf('Importing meetups from meetup.com for %d groups', count($groups)));

        $groupIds = array_column($groups, 'meetup_com_id');

        $meetups = $this->meetupImporter->importForGroupIds($groupIds);
        $this->saveAndReportMeetups($meetups);

        return ShellCode::SUCCESS;
    }
}
