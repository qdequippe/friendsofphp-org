<?php declare(strict_types=1);

namespace Fop\Meetup\Filter;

use Fop\Core\Contract\MeetupFilterInterface;
use Fop\Core\ValueObject\Meetup;
use Nette\Utils\Strings;

final class JavascriptMeetupFilter implements MeetupFilterInterface
{
    /**
     * @param Meetup[] $meetups
     * @return Meetup[]
     */
    public function filter(array $meetups): array
    {
        return array_filter($meetups, function (Meetup $meetup): bool {
            return ! Strings::match($meetup->getName(), '#javascript#i');
        });
    }
}
