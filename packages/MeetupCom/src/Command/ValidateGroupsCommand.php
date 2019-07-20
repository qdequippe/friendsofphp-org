<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Exception\ShouldNotHappenException;
use Fop\Repository\GroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ValidateGroupsCommand extends Command
{
    /**
     * @var string
     */
    private $groupsStorage;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

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

        $existingGroupSlugs = [];
        $duplicatedGroupSlugs = [];

        foreach ($groups as $group) {
            if ($group['country'] === 'United States') {
                throw new ShouldNotHappenException(sprintf(
                    'Group "%s" has country as too generic "%s". Change it to specific state.',
                    $group['name'],
                    'United States'
                ));
            }
        }

        foreach ($groups as $group) {
            if (! isset($existingGroupSlugs[$group['meetup_com_slug']])) {
                $existingGroupSlugs[$group['meetup_com_slug']] = true;
            } else {
                $duplicatedGroupSlugs[] = $group['meetup_com_slug'];
            }
        }

        $duplicatedGroupSlugs = array_unique($duplicatedGroupSlugs);

        if (! count($duplicatedGroupSlugs)) {
            $this->symfonyStyle->success('Great job! There are no duplicated groups.');

            return ShellCode::SUCCESS;
        }

        $this->symfonyStyle->title('Found duplicated groups');
        $this->symfonyStyle->listing($duplicatedGroupSlugs);
        $this->symfonyStyle->error(sprintf('Cleanup "%s" file please.', $this->groupsStorage));

        return ShellCode::ERROR;
    }
}
