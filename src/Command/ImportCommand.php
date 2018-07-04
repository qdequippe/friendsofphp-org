<?php declare(strict_types=1);

namespace Fop\Command;

use Fop\Importer\GroupsFromPhpUgImporter;
use Fop\Importer\MeetupsFromMeetupComImporter;
use Fop\Repository\MeetupRepository;
use Fop\Repository\UserGroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportCommand extends Command
{
    /**
     * @var GroupsFromPhpUgImporter
     */
    private $groupsFromPhpUgImporter;

    /**
     * @var UserGroupRepository
     */
    private $userGroupRepository;

    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    /**
     * @var MeetupsFromMeetupComImporter
     */
    private $meetupsFromMeetupComImporter;

    public function __construct(
        GroupsFromPhpUgImporter $groupsFromPhpUgImporter,
        UserGroupRepository $userGroupRepository,
        MeetupRepository $meetupRepository,
        MeetupsFromMeetupComImporter $meetupsFromMeetupComImporter
    ) {
        parent::__construct();
        $this->groupsFromPhpUgImporter = $groupsFromPhpUgImporter;
        $this->userGroupRepository = $userGroupRepository;
        $this->meetupRepository = $meetupRepository;
        $this->meetupsFromMeetupComImporter = $meetupsFromMeetupComImporter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->importsGroups();
        $this->importMeetups();
    }

    /**
     * @param string[] $userGroup
     */
    private function resolveGroupUrlNameFromGroupUrl(array $userGroup): string
    {
        $array = explode('/', $userGroup['meetup_com_url']);
        end($array);

        return prev($array);
    }

    private function importsGroups(): void
    {
        $groups = $this->groupsFromPhpUgImporter->import();
        $this->userGroupRepository->saveToFile($groups);
    }

    private function importMeetups(): void
    {
        $europeanUserGroups = $this->userGroupRepository->fetchByContinent('Europe');

        $meetups = [];
        foreach ($europeanUserGroups as $europeanUserGroup) {
            $groupUrlName = $this->resolveGroupUrlNameFromGroupUrl($europeanUserGroup);

            $meetupsOfGroup = $this->meetupsFromMeetupComImporter->importForGroupName($groupUrlName);
            $meetups = array_merge($meetups, $meetupsOfGroup);
        }

        $this->meetupRepository->saveToFile($meetups);
    }
}
