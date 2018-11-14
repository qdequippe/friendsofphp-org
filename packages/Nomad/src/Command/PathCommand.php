<?php declare(strict_types=1);

namespace Fop\Nomad\Command;

use DateTimeInterface;
use Fop\Entity\Meetup;
use Fop\Nomad\NomadPolygonFactory;
use Fop\Nomad\TravelingSalesman\MeetupDistanceEvaluator;
use Fop\Nomad\ValueObject\TimeSpan;
use Fop\Repository\MeetupRepository;
use Location\Polygon;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

/**
 * @see https://developers.google.com/optimization/routing/tsp
 * @see https://github.com/mjaschen/phpgeo/blob/master/README.md
 * @see http://www.srimax.com/index.php/travelling-salesman-problem-using-branch-bound-approach-php/
 */
final class PathCommand extends Command
{
    /**
     * @var int[]
     */
    private $path = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    /**
     * @var DateTimeInterface
     */
    private $startDateTime;

    /**
     * @var DateTimeInterface
     */
    private $endDateTime;

    /**
     * @var MeetupDistanceEvaluator
     */
    private $meetupDistanceEvaluator;

    /**
     * @var NomadPolygonFactory
     */
    private $nomadPolygonFactory;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        MeetupRepository $meetupRepository,
        int $startDate,
        int $endDate,
        NomadPolygonFactory $nomadPolygonFactory,
        MeetupDistanceEvaluator $meetupDistanceEvaluator
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->meetupRepository = $meetupRepository;
        $this->startDateTime = DateTime::from($startDate);
        $this->endDateTime = DateTime::from($endDate);
        $this->meetupDistanceEvaluator = $meetupDistanceEvaluator;
        $this->nomadPolygonFactory = $nomadPolygonFactory;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Finds best way to travel round meetups in specific area');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $meetups = $this->filterMeetupsOutOfTimeAndSpace($this->meetupRepository->fetchAllAsObjects());

        // 1. create data - https://developers.google.com/optimization/routing/tsp#dist_matrix_data
        $meetupRatesMatrix = $this->createRateMatrix($meetups);

        // set starting meetup
        $this->path[] = 0;

        // 2. get path
        // make null pro impossible
        $meetupCount = count($meetups);
        // we start from line 1
        for ($x = 1; $x < $meetupCount; ++$x) {
            $currentLine = $this->createNextLine($meetupCount, $meetupRatesMatrix, $x);

            // no more path
            if ($currentLine === []) {
                break;
            }

            $bestPathIndex = array_search(min($currentLine), $currentLine, true);
            if (in_array($bestPathIndex, $this->path, true)) {
                break;
            }

            $this->path[] = $bestPathIndex;

            // jump!
            $x = $bestPathIndex;
            // @todo break after 12 steps or runnout of options
        }

        $this->printPath($meetups);

        return ShellCode::SUCCESS;
    }

    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    private function filterMeetupsOutOfTimeAndSpace(array $meetups): array
    {
        $polygon = $this->nomadPolygonFactory->create();
        $timeSpan = new TimeSpan($this->startDateTime, $this->endDateTime);

        foreach ($meetups as $key => $meetup) {
            // filter out meetups not located in the polygon
            if (! $polygon->contains($meetup->getCoordinate())) {
                unset($meetups[$key]);
            }

            // filter out meetups out of date
            if (! $timeSpan->containsDateTime($meetup->getStartDateTime())) {
                unset($meetups[$key]);
            }
        }

        return array_values($meetups);
    }

    /**
     * @param Meetup[] $meetups
     * @return float[][]|null[][]
     */
    private function createRateMatrix(array $meetups): array
    {
        $rateMatrix = [];

        foreach ($meetups as $x => $meetup) {
            foreach ($meetups as $y => $anotherMeetup) {
                $rateMatrix[$x][$y] = $this->meetupDistanceEvaluator->evaluate($meetup, $anotherMeetup);
            }
        }

        return $rateMatrix;
    }

    /**
     * @param mixed[] $meetupRatesMatrix
     * @return float[]
     */
    private function createNextLine(int $meetupCount, array $meetupRatesMatrix, int $x): array
    {
        $currentLine = [];

        for ($y = 0; $y < $meetupCount; ++$y) {
            if ($this->shouldSkipMatrixNot($meetupRatesMatrix, $x, $y)) {
                continue;
            }

            $currentLine[$y] = $meetupRatesMatrix[$x][$y];
        }

        return $currentLine;
    }

    /**
     * @param Meetup[] $meetups
     */
    private function printPath(array $meetups): void
    {
        foreach ($this->path as $path) {
            $meetup = $meetups[$path];

            $this->symfonyStyle->section($meetup->getName());
            $this->symfonyStyle->writeln(' * ' . $meetup->getStartDateTime()->format('Y-m-d'));
            $this->symfonyStyle->writeln(' * ' . $meetup->getUrl());
            $this->symfonyStyle->newLine();
        }
    }

    /**
     * @param float[]|null[] $matrixRates
     */
    private function shouldSkipMatrixNot(array $matrixRates, int $x, int $y): bool
    {
        if ($matrixRates[$x][$y] === null) {
            return true;
        }

        return (bool) array_intersect([$x, $y], $this->path);
    }
}
