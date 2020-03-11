<?php declare(strict_types=1);

namespace Fop\Core\Filter;

use DateTimeInterface;
use Fop\Core\Contract\MeetupFilterInterface;
use Fop\Core\ValueObject\Meetup;
use Nette\Utils\DateTime;

final class TooFarMeetupFilter implements MeetupFilterInterface
{
    /**
     * @var DateTimeInterface
     */
    private $maxForecastDateTime;

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
        return array_filter($meetups, function (Meetup $meetup): bool {
            return $meetup->getStartDateTime() <= $this->maxForecastDateTime;
        });
    }
}
