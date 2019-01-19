<?php declare(strict_types=1);

namespace Fop\Command;

use DateTimeInterface;
use Fop\Entity\Meetup;
use Fop\MeetupCom\Command\Reporter\MeetupReporter;
use Fop\Repository\MeetupRepository;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractImportCommand extends Command
{
    /**
     * @var int
     */
    protected $maxForecastDays;

    /**
     * @var SymfonyStyle
     */
    protected $symfonyStyle;

    /**
     * @var DateTimeInterface
     */
    protected $maxForecastDateTime;

    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    /**
     * @var MeetupReporter
     */
    private $meetupReporter;

    /**
     * @required
     */
    public function setAbstractImportRequirements(
        SymfonyStyle $symfonyStyle,
        int $maxForecastDays,
        MeetupRepository $meetupRepository,
        MeetupReporter $meetupReporter
    ): void {
        $this->symfonyStyle = $symfonyStyle;
        $this->maxForecastDays = $maxForecastDays;
        $this->meetupRepository = $meetupRepository;
        $this->meetupReporter = $meetupReporter;

        $this->maxForecastDateTime = DateTime::from('+' . $maxForecastDays . 'days');
    }

    /**
     * @param Meetup[] $meetups
     */
    protected function saveAndReportMeetups(array $meetups): void
    {
        if (count($meetups) === 0) {
            return;
        }

        $this->meetupReporter->printMeetups($meetups);

        $this->meetupRepository->saveImportsToFile($meetups, $this->getSourceName());
        $this->symfonyStyle->success(
            sprintf('Loaded %d meetups for next %d days', count($meetups), $this->maxForecastDays)
        );
    }

    abstract protected function getSourceName(): string;
}
