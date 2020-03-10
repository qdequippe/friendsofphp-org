<?php declare(strict_types=1);

namespace Fop\MeetupCom\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LastMeetupDateByGroupSlugTwigExtension extends AbstractExtension
{
    /**
     * @var string[]
     */
    private $lastMeetupDateByGroupSlug = [];

    /**
     * @param string[] $lastMeetupDateByGroupSlug
     */
    public function __construct(array $lastMeetupDateByGroupSlug = [])
    {
        $this->lastMeetupDateByGroupSlug = $lastMeetupDateByGroupSlug;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('last_meetup_date_by_group_slug', function (string $groupSlug): ?string {
                return $this->lastMeetupDateByGroupSlug[$groupSlug] ?? null;
            }),
        ];
    }
}
