<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\FileSystem\YamlFileSystem;
use Fop\MeetupCom\Api\MeetupComApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class SearchPhpGroupsCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    /**
     * @var YamlFileSystem
     */
    private $yamlFileSystem;

    /**
     * @var string
     */
    private $foundMeetupsStorageFile;

    public function __construct(
        MeetupComApi $meetupComApi,
        SymfonyStyle $symfonyStyle,
        YamlFileSystem $yamlFileSystem,
        string $foundMeetupsStorageFile
    ) {
        parent::__construct();
        $this->meetupComApi = $meetupComApi;
        $this->symfonyStyle = $symfonyStyle;
        $this->yamlFileSystem = $yamlFileSystem;
        $this->foundMeetupsStorageFile = $foundMeetupsStorageFile;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Search for all PHP groups on meetup.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->symfonyStyle->note('Searching meetups with "php" from meetup.com');

        $foundMeetups = $this->meetupComApi->findMeetupsGroupsByKeyword('php');
        $data = [
            'parameters' => [
                'meetups' => $foundMeetups,
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($data, $this->foundMeetupsStorageFile);

        $this->symfonyStyle->success(sprintf('Done - %d meetups added', count($foundMeetups)));
    }
}
