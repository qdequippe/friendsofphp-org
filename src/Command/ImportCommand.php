<?php declare(strict_types=1);

namespace Fop\Command;

use Fop\Entity\Group;
use Fop\Importer\GroupsFromPhpUgImporter;
use Fop\Importer\MeetupsFromMeetupComImporter;
use Fop\Repository\GroupRepository;
use Fop\Repository\MeetupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportCommand extends Command
{
    /**
     * @var string
     */
    private const OPTION_GROUPS = 'groups';

    /**
     * @var string
     */
    private const OPTION_MEETUPS = 'meetups';

    /**
     * @var GroupsFromPhpUgImporter
     */
    private $groupsFromPhpUgImporter;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    /**
     * @var MeetupsFromMeetupComImporter
     */
    private $meetupsFromMeetupComImporter;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var int
     */
    private $maxForecastDays;

    public function __construct(
        GroupsFromPhpUgImporter $groupsFromPhpUgImporter,
        GroupRepository $userGroupRepository,
        MeetupRepository $meetupRepository,
        MeetupsFromMeetupComImporter $meetupsFromMeetupComImporter,
        SymfonyStyle $symfonyStyle,
        int $maxForecastDays
    ) {
        parent::__construct();
        $this->groupsFromPhpUgImporter = $groupsFromPhpUgImporter;
        $this->groupRepository = $userGroupRepository;
        $this->meetupRepository = $meetupRepository;
        $this->meetupsFromMeetupComImporter = $meetupsFromMeetupComImporter;
        $this->symfonyStyle = $symfonyStyle;
        $this->maxForecastDays = $maxForecastDays;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->addOption(self::OPTION_GROUPS, null, InputOption::VALUE_NONE, 'Imports groups from php.ug');
        $this->addOption(self::OPTION_MEETUPS, null, InputOption::VALUE_NONE, 'Imports meetups meetup.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (! $input->getOption(self::OPTION_GROUPS) && ! $input->getOption(self::OPTION_MEETUPS)) {
            $this->symfonyStyle->note(
                sprintf('Add "--%s" or "--%s" option to start import.', self::OPTION_GROUPS, self::OPTION_MEETUPS)
            );
            return;
        }

        if ($input->getOption(self::OPTION_GROUPS)) {
            $this->symfonyStyle->note('Importing groups from php.ug');
            $this->importsGroups();
            $this->symfonyStyle->success('Done');
        }

        if ($input->getOption(self::OPTION_MEETUPS)) {
            $this->symfonyStyle->note('Importing meetups from meetup.com');
            $this->importMeetups();
            $this->symfonyStyle->success('Done');
        }
    }

    private function importsGroups(): void
    {
        $groupsByContinent = $this->groupsFromPhpUgImporter->import();
        foreach ($groupsByContinent as $groups) {
            /** @var Group $group */
            foreach ($groups as $group) {
                $this->symfonyStyle->note(sprintf('Groups "%s" imported', $group->getName()));
            }
        }
        $this->groupRepository->saveImportToFile($groupsByContinent);
    }

    private function importMeetups(): void
    {
        $europeanGroups = $this->groupRepository->fetchByContinent('Europe');

        $groupIds = array_column($europeanGroups, 'meetup_com_id');
        $meetups = $this->meetupsFromMeetupComImporter->importForGroupIds($groupIds);

        $this->symfonyStyle->note(sprintf('Loaded %d meetups for next %d days', count($meetups), $this->maxForecastDays));

        $this->meetupRepository->saveImportsToFile($meetups);
    }
}
