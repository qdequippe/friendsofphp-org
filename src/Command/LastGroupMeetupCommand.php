<?php declare(strict_types=1);

namespace Fop\Command;

use Fop\FileSystem\YamlFileSystem;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\Repository\GroupRepository;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class LastGroupMeetupCommand extends Command
{
    /**
     * @var string
     */
    private $generatedDataStorage;

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
     * @var YamlFileSystem
     */
    private $yamlFileSystem;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        GroupRepository $groupRepository,
        MeetupComApi $meetupComApi,
        YamlFileSystem $yamlFileSystem,
        string $generatedDataStorage
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->groupRepository = $groupRepository;
        $this->meetupComApi = $meetupComApi;
        $this->yamlFileSystem = $yamlFileSystem;
        $this->generatedDataStorage = $generatedDataStorage;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Import the last group meetup from meetup.com to see which are active and which not');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $groupSlugs = $this->groupRepository->fetchGroupSlugs();

        $yamlStructure = [
            'parameters' => [
                'last_meetup_date_by_group_slug' => $this->collectLastMeetupDateByGroupSlug($groupSlugs),
            ],
        ];

        $storage = $this->generatedDataStorage . '/group_last_date.yaml';
        $this->yamlFileSystem->saveArrayToFile($yamlStructure, $storage);
        $this->symfonyStyle->success('Dump is done!');

        return ShellCode::SUCCESS;
    }

    /**
     * @param string[] $groupSlugs
     * @return string[]
     */
    private function collectLastMeetupDateByGroupSlug(array $groupSlugs): array
    {
        $progressBar = $this->symfonyStyle->createProgressBar(count($groupSlugs));

        $lastMeetupByGroupSlug = [];
        foreach ($groupSlugs as $groupSlug) {
            try {
                $lastMeetupData = $this->meetupComApi->getLastMeetupByGroupSlug($groupSlug);
                $progressBar->advance();

                if ($lastMeetupData === []) {
                    // never
                    $lastMeetupByGroupSlug[$groupSlug] = false;
                    continue;
                }

                $lastMeetupByGroupSlug[$groupSlug] = $lastMeetupData['local_date'];
            } catch (GuzzleException $guzzleException) {
                // the group might not exists anymore, but it should not be a blocker for existing groups
                continue;
            }
        }

        arsort($lastMeetupByGroupSlug);

        return $lastMeetupByGroupSlug;
    }
}
