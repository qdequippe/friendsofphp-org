<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command\Reporter;

use Fop\Entity\Meetup;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MeetupReporter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param Meetup[] $meetups
     */
    public function printMeetups(array $meetups): void
    {
        $meetupListToDisplay = [];
        foreach ($meetups as $meetup) {
            $meetupListToDisplay[] = $meetup->getStartDateTime()->format('Y-m-d') . ' - ' . $meetup->getName();
        }

        $this->symfonyStyle->listing($meetupListToDisplay);
    }
}
