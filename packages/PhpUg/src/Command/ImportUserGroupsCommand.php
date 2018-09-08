<?php declare(strict_types=1);

namespace Fop\PhpUg\Command;

use Fop\PhpUg\UserGroupImporter;
use Fop\Repository\GroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportUserGroupsCommand extends Command
{
    /**
     * @var UserGroupImporter
     */
    private $userGroupImporter;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        UserGroupImporter $userGroupImporter,
        GroupRepository $userGroupRepository,
        SymfonyStyle $symfonyStyle
    ) {
        parent::__construct();
        $this->userGroupImporter = $userGroupImporter;
        $this->groupRepository = $userGroupRepository;
        $this->symfonyStyle = $symfonyStyle;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import groups from php.ug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->symfonyStyle->note('Importing groups from php.ug');

        $groups = $this->userGroupImporter->import();
        foreach ($groups as $group) {
            $this->symfonyStyle->note(sprintf('Group "%s" imported', $group->getName()));
        }
        $this->groupRepository->saveImportToFile($groups);

        $this->symfonyStyle->success('Done');
    }
}
