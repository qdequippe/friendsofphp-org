<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

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
use function Safe\sprintf;

final class ShowMeetupDetailCommand extends Command
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

    public function __construct(
        SymfonyStyle $symfonyStyle,
        GroupRepository $groupRepository,
        GroupDetailResolver $groupDetailResolver
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->groupRepository = $groupRepository;
        $this->groupDetailResolver = $groupDetailResolver;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription(
            'Shows details for meetup group(s). You can provide either 1 group link: "bin/console meetup-com-group-detail https://www.meetup.com/Berlin-PHP-Usergroup/", or a file with multiple such links, each on single row.'
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
            $this->printGroup($group);
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

            $this->printGroup($group);
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
     * @param mixed[] $group
     */
    private function printGroup(array $group): void
    {
        $this->symfonyStyle->writeln(sprintf("        -   name: '%s'", str_replace("'", '"', $group['name'])));
        $this->symfonyStyle->writeln(sprintf('            meetup_com_id: %s', $group['id']));
        $this->symfonyStyle->writeln(sprintf("            meetup_com_url: '%s'", $group['link']));
        $this->symfonyStyle->writeln(sprintf("            country: '%s'", $group['country']));
        $this->symfonyStyle->newLine();
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
