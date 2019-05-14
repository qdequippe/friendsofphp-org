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
            // usage in Twig: {% set meetups = filter_meetups_including_tag(meetups, 'wordpress') %}
            'filter_meetups_including_tags' => function (array $meetups, $tags): array {
                return $this->filterMeetupsIncludingTags($meetups, $tags);
            },

            // usage in Twig: {{ count_meetups_including_tags(meetups, 'wordpress') }}
            'count_meetups_including_tags' => function (array $meetups, $tags): int {
                return count($this->filterMeetupsIncludingTags($meetups, $tags));
            },

            // usage in Twig: {% set meetups = filter_meetups_excluding_tag(meetups, 'wordpress') %}
            'filter_meetups_excluding_tags' => function (array $meetups, $tags): array {
                if (is_string($tags)) {
                    $tags = [$tags];
                }

                return array_filter($meetups, function ($meetup) use ($tags): bool {
                    return ! array_intersect($tags, $meetup['tags']);
                });
            },
        ];
    }

    /**
     * @param mixed[] $meetups
     * @param string|string[] $tags
     * @return mixed[]
     */
    private function filterMeetupsIncludingTags(array $meetups, $tags): array
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }

        return array_filter($meetups, function ($meetup) use ($tags): bool {
            return (bool) array_intersect($tags, $meetup['tags']);
        });
    }
}
