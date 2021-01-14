<?php

declare(strict_types=1);

namespace Fop\Meetup\Filter;

use Fop\Core\ValueObject\Option;
use Fop\Meetup\Contract\MeetupFilterInterface;
use Fop\Meetup\ValueObject\Meetup;
use Nette\Utils\DateTime;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class TooFarMeetupFilter implements MeetupFilterInterface
{
    private DateTime $maxForecastDateTime;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $maxForecastDays = $parameterProvider->provideIntParameter(Option::MAX_FORECAST_DAYS);
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
