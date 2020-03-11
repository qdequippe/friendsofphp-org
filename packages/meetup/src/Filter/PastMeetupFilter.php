<?php declare(strict_types=1);

namespace Fop\Meetup\Filter;

use Fop\Core\ValueObject\Meetup;
use Fop\Meetup\Contract\MeetupFilterInterface;
use Nette\Utils\DateTime;

final class PastMeetupFilter implements MeetupFilterInterface
{
    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    public function filter(array $meetups): array
    {
        return array_filter($meetups, function (Meetup $meetup): bool {
            return $meetup->getStartDateTime() > DateTime::from('now');
        });
    }
}
