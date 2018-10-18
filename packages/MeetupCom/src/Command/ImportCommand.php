<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\MeetupCom\MeetupImporter;
use Fop\Repository\GroupRepository;
use Fop\Repository\MeetupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportCommand extends Command
{
    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    /**
     * @var MeetupImporter
     */
    private $meetupImporter;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var int
     */
    private $maxForecastDays;

    public function __construct(
        GroupRepository $userGroupRepository,
        MeetupRepository $meetupRepository,
        MeetupImporter $meetupImporter,
        SymfonyStyle $symfonyStyle,
        int $maxForecastDays
    ) {
        parent::__construct();
        $this->groupRepository = $userGroupRepository;
        $this->meetupRepository = $meetupRepository;
        $this->meetupImporter = $meetupImporter;
        $this->symfonyStyle = $symfonyStyle;
        $this->maxForecastDays = $maxForecastDays;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import meetups from meetup.com based on group ids.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $groups = $this->groupRepository->fetchAll();

        $this->symfonyStyle->note(sprintf('Importing meetups from meetup.com for %d groups', count($groups)));

        $groupIds = array_column($groups, 'meetup_com_id');

        $meetups = $this->meetupImporter->importForGroupIds($groupIds);

        $meetupListToDisplay = [];
        foreach ($meetups as $meetup) {
            $meetupListToDisplay[] = $meetup->getStartDateTime()->format('Y-m-d') . ' - ' . $meetup->getName();
        }
        $this->symfonyStyle->listing($meetupListToDisplay);
        $this->symfonyStyle->note(
            sprintf('Loaded %d meetups for next %d days', count($meetups), $this->maxForecastDays)
        );

        $this->meetupRepository->saveImportsToFile($meetups);

        $this->symfonyStyle->success('Done');
    }
}
