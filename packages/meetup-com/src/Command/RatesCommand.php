<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\MeetupCom\Api\MeetupComApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class RatesCommand extends Command
{
    public function __construct(private MeetupComApi $meetupComApi, private SymfonyStyle $symfonyStyle)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Check API rates');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rateLimits = $this->meetupComApi->getRateLimits();

        $this->symfonyStyle->note('Request limit: ' . $rateLimits->getRequestLimit());
        $this->symfonyStyle->note('Remaining request: ' . $rateLimits->getRemainingRequests());
        $this->symfonyStyle->note('Request count to reset: ' . $rateLimits->getRequestToReset());

        return ShellCode::SUCCESS;
    }
}
