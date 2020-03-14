<?php declare(strict_types=1);

namespace Fop\Meetup\Filter;

use Fop\Meetup\Contract\MeetupFilterInterface;
use Fop\Meetup\ValueObject\Meetup;
use Nette\Utils\DateTime;

final class TooFarMeetupFilter implements MeetupFilterInterface
{
    private DateTime $maxForecastDateTime;

    public function __construct(int $maxForecastDays)
    {
        $this->maxForecastDateTime = DateTime::from('+' . $maxForecastDays . 'days');
    }

    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    public function filter(array $meetups): array
    {
        return array_filter(
            $meetups,
            fn (Meetup $meetup): bool => $meetup->getStartDateTime() <= $this->maxForecastDateTime
        );
    }
}
