<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Command\Reporter;

use Fop\Meetup\ValueObject\Meetup;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MeetupReporter
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle
    ) {
    }

    /**
     * @param Meetup[] $meetups
     */
    public function reportMeetups(array $meetups): void
    {
        if ($meetups === []) {
            return;
        }

        $successMessage = sprintf('Loaded %d meetups', count($meetups));
        $this->symfonyStyle->success($successMessage);

        $this->printMeetups($meetups);
        $this->symfonyStyle->newLine(2);
    }

    /**
     * @param Meetup[] $meetups
     */
    private function printMeetups(array $meetups): void
    {
        $meetupListToDisplay = [];
        foreach ($meetups as $meetup) {
            $meetupListToDisplay[] = $meetup->getStartDateTimeFormatted('Y-m-d') . ' - ' . $meetup->getName();
        }

        $this->symfonyStyle->listing($meetupListToDisplay);
    }
}
