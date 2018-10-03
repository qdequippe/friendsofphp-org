<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\Country\CountryResolver;
use Fop\MeetupCom\Api\MeetupComApi;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ShowMeetupDetailCommand extends Command
{
    /**
     * @var string
     */
    private const ARGUMENT_SOURCE = 'source';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    /**
     * @var CountryResolver
     */
    private $countryResolver;

    public function __construct(
        MeetupComApi $meetupComApi,
        SymfonyStyle $symfonyStyle,
        CountryResolver $countryResolver
    ) {
        parent::__construct();
        $this->symfonyStyle = $symfonyStyle;
        $this->meetupComApi = $meetupComApi;
        $this->countryResolver = $countryResolver;
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
            'Group url on meetup.com, e.g. https://www.meetup.com/Berlin-PHP-Usergroup/'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var string $source */
        $source = $input->getArgument(self::ARGUMENT_SOURCE);

        if (is_file($source)) {
            $fileContent = FileSystem::read($source);
            $groupUrls = explode(PHP_EOL, $fileContent);
            // remove empty
            $groupUrls = array_filter($groupUrls);

            foreach ($groupUrls as $groupUrl) {
                $group = $this->getGroupDetailForMeetupComUrl($groupUrl);
                $this->printGroup($group);
            }
        } else {
            $group = $this->getGroupDetailForMeetupComUrl($source);
            $this->printGroup($group);
        }
    }

    /**
     * @return mixed[]
     */
    private function getGroupDetailForMeetupComUrl(string $source): array
    {
        $group = $this->meetupComApi->getGroupForUrl($source);
        $group['country'] = $this->countryResolver->resolveFromGroup($group);

        return $group;
    }

    /**
     * @param mixed[] $group
     */
    private function printGroup(array $group): void
    {
        $this->symfonyStyle->writeln(sprintf("    -   name: '%s'", $group['name']));
        $this->symfonyStyle->writeln(sprintf('        meetup_com_id: %s', $group['id']));
        $this->symfonyStyle->writeln(sprintf("        meetup_com_url: '%s'", $group['link']));
        $this->symfonyStyle->writeln(sprintf("        country: '%s'", $group['country']));
        $this->symfonyStyle->newLine();
    }
}
