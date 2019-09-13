<?php declare(strict_types=1);

namespace Fop\MeetupCom\Twig;

use Symplify\Statie\Contract\Templating\FilterProviderInterface;

final class LastMeetupDateByGroupSlugFilterProvider implements FilterProviderInterface
{
    /**
     * @var string[]
     */
    private $lastMeetupDateByGroupSlug = [];

    /**
     * @param string[] $lastMeetupDateByGroupSlug
     */
    public function __construct(array $lastMeetupDateByGroupSlug)
    {
        $this->lastMeetupDateByGroupSlug = $lastMeetupDateByGroupSlug;
    }

    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            'last_meetup_date_by_group_slug' => function (string $groupSlug): ?string {
                return $this->lastMeetupDateByGroupSlug[$groupSlug] ?? null;
            },
        ];
    }
}
