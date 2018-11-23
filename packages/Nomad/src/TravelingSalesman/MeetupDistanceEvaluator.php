<?php declare(strict_types=1);

namespace Fop\Nomad\TravelingSalesman;

use Fop\Entity\Meetup;
use Location\Distance\Vincenty;

final class MeetupDistanceEvaluator
{
    /**
     * @var Vincenty
     */
    private $vincenty;

    public function __construct(Vincenty $vincenty)
    {
        $this->vincenty = $vincenty;
    }

    public function getHash(Meetup $meetup, Meetup $anotherMeetup): string
    {
        return md5(spl_object_hash($meetup) . spl_object_hash($anotherMeetup));
    }

    public function evaluate(Meetup $meetup, Meetup $anotherMeetup): ?float
    {
        if ($this->shouldSkip($meetup, $anotherMeetup)) {
            return null;
        }

        return $this->computeTimeRate($meetup, $anotherMeetup) + $this->computeLocationRate($meetup, $anotherMeetup);
    }

    private function shouldSkip(Meetup $meetup, Meetup $anotherMeetup): bool
    {
        // the same meetup
        if ($meetup === $anotherMeetup) {
            return true;
        }

        // the same date
        if ($meetup->getStartDateTime()->format('Y-m-d') === $anotherMeetup->getStartDateTime()->format('Y-m-d')) {
            return true;
        }

        // from the future to the past
        if ($meetup->getStartDateTime() > $anotherMeetup->getStartDateTime()) {
            return true;
        }

        return false;
    }

    private function computeTimeRate(Meetup $meetup, Meetup $anotherMeetup): float
    {
        $diff = $meetup->getStartDateTime()->diff($anotherMeetup->getStartDateTime());

        return $diff->days ** 5;
    }

    private function computeLocationRate(Meetup $meetup, Meetup $anotherMeetup): float
    {
        return 0;
        $distance = $this->vincenty->getDistance($meetup->getCoordinate(), $anotherMeetup->getCoordinate());
        return ($distance / 10) ** 2;
    }
}
