<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Core\Exception\ShouldNotHappenException;
use Fop\Core\ValueObject\Option;
use Fop\Meetup\Repository\GroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ValidateGroupsCommand extends Command
{
    private string $groupsStorage;

    public function __construct(
        private SymfonyStyle $symfonyStyle,
        private GroupRepository $groupRepository,
        ParameterProvider $parameterProvider
    ) {
        parent::__construct();

        $this->groupsStorage = $parameterProvider->provideStringParameter(Option::GROUPS_STORAGE);
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
            if ($group->getCountry() === 'United States') {
                throw new ShouldNotHappenException(sprintf(
                    'Group "%s" has country as too generic "%s". Change it to specific state.',
                    $group->getName(),
                    'United States'
                ));
            }
        }

        foreach ($groups as $group) {
            if (! isset($existingGroupSlugs[$group->getMeetupComSlug()])) {
                $existingGroupSlugs[$group->getMeetupComSlug()] = true;
            } else {
                $duplicatedGroupSlugs[] = $group->getMeetupComSlug();
            }
        }

        $duplicatedGroupSlugs = array_unique($duplicatedGroupSlugs);

        if (! count($duplicatedGroupSlugs)) {
            $this->symfonyStyle->success('Great job! There are no duplicated groups.');

            return ShellCode::SUCCESS;
        }

        $this->symfonyStyle->section('Found duplicated groups');
        $this->symfonyStyle->listing($duplicatedGroupSlugs);

        $errorMessage = sprintf('Cleanup "%s" file', $this->groupsStorage);
        $this->symfonyStyle->error($errorMessage);

        return ShellCode::ERROR;
    }
}
