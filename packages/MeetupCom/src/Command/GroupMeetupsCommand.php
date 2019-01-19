<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\MeetupCom\Command\Reporter\MeetupReporter;
use Fop\MeetupCom\MeetupImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use function Safe\sprintf;

final class GroupMeetupsCommand extends Command
{
    /**
     * @var string
     */
    private const GROUP_ID = 'group-id';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MeetupImporter
     */
    private $meetupImporter;

    /**
     * @var MeetupReporter
     */
    private $meetupReporter;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        MeetupImporter $meetupImporter,
        MeetupReporter $meetupReporter
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->meetupImporter = $meetupImporter;
        $this->meetupReporter = $meetupReporter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show meetups for group id');
        $this->addArgument(self::GROUP_ID, InputArgument::REQUIRED, 'Group id, e.g. 3964682');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var int $groupId */
        $groupId = (int) $input->getArgument(self::GROUP_ID);

        $meetups = $this->meetupImporter->importForGroupIds([$groupId]);
        $this->meetupReporter->printMeetups($meetups);

        $this->symfonyStyle->success(sprintf('Found %d meetups', count($meetups)));

        return ShellCode::SUCCESS;
    }
}
