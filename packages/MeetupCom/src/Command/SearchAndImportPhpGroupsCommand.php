<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Country\CountryResolver;
use Fop\FileSystem\YamlFileSystem;
use Fop\MeetupCom\Api\MeetupComApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class SearchAndImportPhpGroupsCommand extends Command
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

    /**
     * @var CountryResolver
     */
    private $countryResolver;

    public function __construct(
        MeetupComApi $meetupComApi,
        SymfonyStyle $symfonyStyle,
        YamlFileSystem $yamlFileSystem,
        string $foundMeetupsStorageFile,
        CountryResolver $countryResolver
    ) {
        parent::__construct();
        $this->meetupComApi = $meetupComApi;
        $this->symfonyStyle = $symfonyStyle;
        $this->yamlFileSystem = $yamlFileSystem;
        $this->foundMeetupsStorageFile = $foundMeetupsStorageFile;
        $this->countryResolver = $countryResolver;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Search for all PHP groups on meetup.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->symfonyStyle->note('Searching meetups with "php" from meetup.com');

        $rawFoundGroups = $this->meetupComApi->findMeetupsGroupsByKeywords();
        $groups = [];

        foreach ($rawFoundGroups as $rawFoundGroup) {
            $groups[] = [
                'name' => $rawFoundGroup['name'],
                'meetup_com_id' => $rawFoundGroup['id'],
                'meetup_com_url' => $rawFoundGroup['link'],
                'country' => $this->countryResolver->resolveFromGroup($rawFoundGroup),
            ];
        }

        $data = [
            'parameters' => [
                'groups' => $groups,
            ],
        ];

        $this->yamlFileSystem->saveArrayToFile($data, $this->foundMeetupsStorageFile);

        $this->symfonyStyle->success(sprintf('Done - %d groups added', count($groups)));
    }
}
