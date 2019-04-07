<?php declare(strict_types=1);

namespace Fop\Tag;

use Nette\Utils\Strings;

final class MeetupTagResolver
{
    /**
     * @var string[][]
     */
    private $tagsByMatches = [
        # tag => [matches]
        'wordpress' => ['wordpress', 'wp', 'ThinkWP'],
        'drupal' => ['drupal'],
        'magento' => ['magento'],
        'symfony' => ['symfony'],
        'laravel' => ['laravel'],
    ];

    /**
     * @return string[]
     */
    public function resolveFromName(string $name, string $groupName): array
    {
        $tags = [];

        foreach ($this->tagsByMatches as $tag => $matches) {
            foreach ($matches as $match) {
                if (Strings::match($name, '#' . preg_quote($match, '#') . '#i')) {
                    /** @var string $tag */
                    $tags[] = $tag;
                }

                if (Strings::match($groupName, '#' . preg_quote($match, '#') . '#i')) {
                    /** @var string $tag */
                    $tags[] = $tag;
                }
            }
        }

        return array_unique($tags);
    }
}
