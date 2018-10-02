<?php declare(strict_types=1);

namespace Fop\Twig;

use Symplify\Statie\Contract\Templating\FilterProviderInterface;

final class SortGroupsFilterProvider implements FilterProviderInterface
{
    /**
     * @return callable[]
     */
    public function provide(): array
    {
        return [
            'sortGroupsByCountry' => function (array $groups): array {
                $groups = $this->arrayUnique($groups);

                usort($groups, function (array $firstGroup, array $secondGroup) {
                    $compareStatus = $firstGroup['country'] <=> $secondGroup['country'];
                    if ($compareStatus !== 0) {
                        return $compareStatus;
                    }

                    return $firstGroup['name'] <=> $secondGroup['name'];
                });

                return $groups;
            },
        ];
    }

    /**
     * @see https://stackoverflow.com/a/946300/1348344
     *
     * @param mixed[] $array
     * @return mixed[]
     */
    private function arrayUnique(array $array): array
    {
        return array_map('unserialize', array_unique(array_map('serialize', $array)));
    }
}
