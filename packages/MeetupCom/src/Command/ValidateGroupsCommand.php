<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Repository\GroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ValidateGroupsCommand extends Command
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
     * @var string
     */
    private $groupsStorage;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        GroupRepository $groupRepository,
        string $groupsStorage
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->groupRepository = $groupRepository;
        $this->groupsStorage = $groupsStorage;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Makes sure the groups are not duplicated.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $groups = $this->groupRepository->fetchAll();

        $existingGroupIds = [];
        $duplicatedGroupIds = [];

        foreach ($groups as $group) {
            if (! isset($existingGroupIds[$group['meetup_com_id']])) {
                $existingGroupIds[$group['meetup_com_id']] = true;
            } else {
                $duplicatedGroupIds[] = $group['meetup_com_id'];
            }
        }

        $duplicatedGroupIds = array_unique($duplicatedGroupIds);

        if (! count($duplicatedGroupIds)) {
            $this->symfonyStyle->success('Great job! There are no duplicated groups.');

            return 0;
        }

        $this->symfonyStyle->title('Found duplicated groups');
        $this->symfonyStyle->listing($duplicatedGroupIds);
        $this->symfonyStyle->error(sprintf('Cleanup "%s" file please.', $this->groupsStorage));

        return 1;
    }
}
