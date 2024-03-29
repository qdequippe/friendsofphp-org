<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Exception\ShouldNotHappenException;
use Fop\Meetup\Repository\GroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ValidateGroupsCommand extends Command
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('validate-groups');
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

        $symfonyStyle = new SymfonyStyle($input, $output);
        if ($duplicatedGroupSlugs === []) {
            $symfonyStyle->success('Great job! There are no duplicated groups.');

            return self::SUCCESS;
        }

        $symfonyStyle->section('Found duplicated groups');
        $symfonyStyle->listing($duplicatedGroupSlugs);

        $errorMessage = sprintf('Cleanup "%s" storage file', $this->groupRepository->getTable());
        $symfonyStyle->error($errorMessage);

        return self::FAILURE;
    }
}
