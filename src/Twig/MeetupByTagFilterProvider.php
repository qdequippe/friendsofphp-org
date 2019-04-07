<?php declare(strict_types=1);

namespace Fop\Twig;

use Symplify\Statie\Contract\Templating\FilterProviderInterface;

final class MeetupByTagFilterProvider implements FilterProviderInterface
{
    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            // usage in Twig: {% set meetups = filter_by_tag(meetups, 'wordpress' %}
            'filter_by_tag' => function (array $meetups, string $tag): array {
                return array_filter($meetups, function ($meetup) use ($tag): bool {
                    return in_array($tag, $meetup['tags'], true);
                });
            },
        ];
    }
}
