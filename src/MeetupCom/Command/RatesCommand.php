<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Command;

use Fop\MeetupCom\Api\MeetupComApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RatesCommand extends Command
{
    public function __construct(
        private readonly MeetupComApi $meetupComApi,
        private readonly SymfonyStyle $symfonyStyle
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('rates');
        $this->setDescription('Check API rates');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rateLimits = $this->meetupComApi->getRateLimits();

        $this->symfonyStyle->writeln('Request limit: ' . $rateLimits->getRequestLimit());
        $this->symfonyStyle->writeln('Remaining request: ' . $rateLimits->getRemainingRequests());
        $this->symfonyStyle->writeln('Request count to reset: ' . $rateLimits->getRequestToReset());

        return self::SUCCESS;
    }
}
