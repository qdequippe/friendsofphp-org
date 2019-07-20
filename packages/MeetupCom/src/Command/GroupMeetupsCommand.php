<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Command\Reporter\MeetupReporter;
use Fop\MeetupCom\Group\GroupDetailResolver;
use Fop\MeetupCom\Meetup\MeetupComMeetupFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class GroupMeetupsCommand extends Command
{
    /**
     * @var string
     */
    private const GROUP_URL = 'group-url';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MeetupReporter
     */
    private $meetupReporter;

    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    /**
     * @var MeetupComMeetupFactory
     */
    private $meetupComMeetupFactory;
    /**
     * @var GroupDetailResolver
     */
    private $groupDetailResolver;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        MeetupReporter $meetupReporter,
        MeetupComApi $meetupComApi,
        MeetupComMeetupFactory $meetupComMeetupFactory,
        GroupDetailResolver $groupDetailResolver
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->meetupReporter = $meetupReporter;
        $this->meetupComApi = $meetupComApi;
        $this->meetupComMeetupFactory = $meetupComMeetupFactory;
        $this->groupDetailResolver = $groupDetailResolver;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show meetups for group url');
        $this->addArgument(
            self::GROUP_URL,
            InputArgument::REQUIRED,
            'Group url, e.g. https://meetups.com/vilniusphp)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $groupUrl */
        $groupUrl = (string) $input->getArgument(self::GROUP_URL);
        $groupSlug = $this->groupDetailResolver->resolveSlugFromUrl($groupUrl);

        $meetups = [];
        foreach ($this->meetupComApi->getMeetupsByGroupSlugs([$groupSlug]) as $data) {
            $meetup = $this->meetupComMeetupFactory->createFromData($data);
            if ($meetup === null) {
                continue;
            }

            $meetups[] = $meetup;
        }

        $this->meetupReporter->printMeetups($meetups);

        $this->symfonyStyle->success(sprintf('Found %d meetups', count($meetups)));

        return ShellCode::SUCCESS;
    }
}
