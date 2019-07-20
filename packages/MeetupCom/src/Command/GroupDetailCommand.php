<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\MeetupCom\Command\Reporter\GroupReporter;
use Fop\MeetupCom\Group\GroupDetailResolver;
use Fop\Repository\GroupRepository;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class GroupDetailCommand extends Command
{
    /**
     * @var string
     */
    private const ARGUMENT_SOURCE = 'source';

    /**
     * @var int[]
     */
    private $alreadyImportedIds = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var GroupDetailResolver
     */
    private $groupDetailResolver;

    /**
     * @var GroupReporter
     */
    private $groupReporter;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        GroupRepository $groupRepository,
        GroupDetailResolver $groupDetailResolver,
        GroupReporter $groupReporter
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->groupRepository = $groupRepository;
        $this->groupDetailResolver = $groupDetailResolver;
        $this->groupReporter = $groupReporter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription(
            'Shows details for group. Provide 1 group link: "bin/console meetup-com-group-detail https://www.meetup.com/Berlin-PHP-Usergroup", or a file with multiple urls, each on new line.'
        );
        $this->addArgument(
            self::ARGUMENT_SOURCE,
            InputArgument::REQUIRED,
            'Group url on meetup.com, e.g. https://www.meetup.com/Berlin-PHP-Usergroup/, or path to file with list of urls separated by newline'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $source */
        $source = $input->getArgument(self::ARGUMENT_SOURCE);

        if (is_file($source)) {
            $this->processFileSource($source);

            return ShellCode::SUCCESS;
        }

        $group = $this->groupDetailResolver->resolveFromUrl($source);

        if ($this->isGroupAlreadyImported($group)) {
            $this->symfonyStyle->error(sprintf('Group "%s" is already imported.', $source));
        } else {
            $this->groupReporter->printGroup($group);
        }

        return ShellCode::SUCCESS;
    }

    private function processFileSource(string $file): void
    {
        $fileContent = FileSystem::read($file);

        $groupUrls = explode(PHP_EOL, $fileContent);
        // remove empty
        $groupUrls = array_filter($groupUrls);

        foreach ($groupUrls as $groupUrl) {
            $group = $this->groupDetailResolver->resolveFromUrl($groupUrl);
            if ($this->isGroupAlreadyImported($group)) {
                $this->symfonyStyle->note(sprintf('Group "%s" is already imported', $groupUrl));
                continue;
            }

            $this->groupReporter->printGroup($group);
        }
    }

    /**
     * @param mixed[] $group
     */
    private function isGroupAlreadyImported(array $group): bool
    {
        if (in_array($group['id'], $this->getAlreadyImportedsIds(), true)) {
            return true;
        }

        $this->alreadyImportedIds[] = $group['id'];

        return false;
    }

    /**
     * @return int[]
     */
    private function getAlreadyImportedsIds(): array
    {
        if (! count($this->alreadyImportedIds)) {
            $this->alreadyImportedIds = array_column($this->groupRepository->fetchAll(), 'meetup_com_id');
        }

        return $this->alreadyImportedIds;
    }
}
